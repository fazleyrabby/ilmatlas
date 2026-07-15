<?php

namespace App\Modules\Scraper\Services;

use App\Modules\Scraper\Contracts\ScraperAdapterInterface;
use App\Modules\Scraper\Models\ScraperSource;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class HtmlAdapter implements ScraperAdapterInterface
{
    private static array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:127.0) Gecko/20100101 Firefox/127.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
    ];

    private int $lastRequestTime = 0;

    public function fetch(ScraperSource $source): string
    {
        $this->respectDelay($source);

        $config = $source->config ?? [];
        $timeout = $config['timeout'] ?? 30;
        $maxRetries = $config['retries'] ?? 3;

        $response = Http::retry($maxRetries, 1000, function ($exception) {
            return $exception instanceof ConnectionException;
        })
            ->timeout($timeout)
            ->withHeaders([
                'User-Agent' => $this->randomUserAgent(),
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ])
            ->get($source->base_url);

        $response->throw();

        return $response->body();
    }

    public function parse(string $rawContent, ScraperSource $source): array
    {
        $crawler = new Crawler($rawContent);
        $config = $source->config ?? [];
        $data = [];

        $feeMapping = $config['fee_selectors'] ?? [];
        foreach ($feeMapping as $field => $selector) {
            try {
                $value = $crawler->filter($selector)->first()->text();
                $data[$field] = trim($value);
            } catch (\Exception $e) {
                $data[$field] = null;
            }
        }

        if (empty($feeMapping)) {
            $data = $this->autoDetectTables($crawler);
        }

        return $data;
    }

    public function normalize(array $parsedData, ScraperSource $source): array
    {
        $normalized = [];

        $mapping = $source->config['field_mapping'] ?? [];
        foreach ($parsedData as $key => $value) {
            $targetKey = $mapping[$key] ?? $key;

            if (is_string($value)) {
                $value = $this->cleanNumericValue($value);
            }

            $normalized[$targetKey] = $value;
        }

        return $normalized;
    }

    public function getConfidence(array $normalizedData): float
    {
        return app(ConfidenceScorer::class)->score(
            trustLevel: 'review_required',
            normalizedData: $normalizedData,
        );
    }

    private function respectDelay(ScraperSource $source): void
    {
        $config = $source->config ?? [];
        $minDelay = $config['min_delay'] ?? 2;
        $maxDelay = $config['max_delay'] ?? 5;

        $elapsed = (microtime(true) - $this->lastRequestTime);
        $delay = rand($minDelay, $maxDelay);

        if ($elapsed < $delay) {
            usleep((int) (($delay - $elapsed) * 1_000_000));
        }

        $this->lastRequestTime = microtime(true);
    }

    private function randomUserAgent(): string
    {
        return self::$userAgents[array_rand(self::$userAgents)];
    }

    private function autoDetectTables(Crawler $crawler): array
    {
        $data = [];

        $crawler->filter('table')->each(function (Crawler $table) use (&$data) {
            $table->filter('tr')->each(function (Crawler $row) use (&$data) {
                $cells = $row->filter('th, td')->each(fn (Crawler $cell) => trim($cell->text()));

                if (count($cells) >= 2) {
                    $key = strtolower(str_replace(' ', '_', $cells[0] ?? ''));
                    $data[$key] = $cells[1] ?? null;
                }
            });
        });

        return $data;
    }

    private function cleanNumericValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $cleaned = preg_replace('/[^\d,.-]/', '', $value);
        $cleaned = str_replace(',', '', $cleaned);

        return $cleaned !== '' ? $cleaned : null;
    }
}
