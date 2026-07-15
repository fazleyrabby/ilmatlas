<?php

namespace App\Modules\Scraper\Services;

use App\Modules\Scraper\Contracts\ScraperAdapterInterface;
use App\Modules\Scraper\Models\ScraperSource;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class InstitutionWebsiteAdapter implements ScraperAdapterInterface
{
    public function fetch(ScraperSource $source): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $source->source_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) EduBaseScraper/1.0');
        $html = curl_exec($ch);
        curl_close($ch);

        return $html ?: '';
    }

    public function parse(string $rawContent, ScraperSource $source): array
    {
        if (empty($rawContent)) {
            return [];
        }

        $crawler = new Crawler($rawContent);
        $data = [];

        if (preg_match('/(\+880\d{9,10}|01\d{9})/', $rawContent, $matches)) {
            $data['phone'] = $matches[1];
        }

        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/', $rawContent, $matches)) {
            $data['email'] = $matches[0];
        }

        $crawler->filter('table')->each(function (Crawler $table) use (&$data) {
            $tableText = $table->text();
            if (preg_match('/(tuition|admission|monthly|session|fee)/i', $tableText)) {
                $table->filter('tr')->each(function (Crawler $row) use (&$data) {
                    $rowText = $row->text();
                    if (preg_match('/(tuition|admission|session|monthly)/i', $rowText) && preg_match('/(\d{3,5})/', $rowText, $m)) {
                        $data['parsed_fees'][] = [
                            'label' => trim(preg_replace('/\s+/', ' ', strtok($rowText, '0123456789৳$'))),
                            'amount' => (float) $m[1]
                        ];
                    }
                });
            }
        });

        $facilitiesKeywords = ['library', 'computer lab', 'playground', 'cctv', 'auditorium', 'canteen'];
        foreach ($facilitiesKeywords as $fac) {
            if (stripos($rawContent, $fac) !== false) {
                $data['facilities'][] = str_replace(' ', '-', $fac);
            }
        }

        return $data;
    }

    public function normalize(array $parsedData, ScraperSource $source): array
    {
        $normalizedFees = [];
        foreach ($parsedData['parsed_fees'] ?? [] as $fee) {
            $slug = Str::slug($fee['label']);
            if (empty($slug)) {
                $slug = 'tuition-fee';
            }
            $freq = 'monthly';
            if (str_contains($slug, 'admission') || str_contains($slug, 'session')) {
                $freq = 'one_time';
            }
            $normalizedFees[] = [
                'fee_type_slug' => $slug,
                'amount' => $fee['amount'],
                'frequency' => $freq,
            ];
        }

        return [
            'institute_id' => $source->institute_id,
            'contacts' => array_values(array_filter([
                ['type' => 'phone', 'value' => $parsedData['phone'] ?? null],
                ['type' => 'email', 'value' => $parsedData['email'] ?? null],
            ], fn ($c) => !empty($c['value']))),
            'fees' => $normalizedFees,
            'facilities' => $parsedData['facilities'] ?? [],
        ];
    }

    public function getConfidence(array $normalizedData): float
    {
        return 95.0;
    }
}
