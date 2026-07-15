<?php

namespace App\Modules\Scraper\Commands;

use App\Modules\Scraper\Models\ScraperSource;
use App\Modules\Scraper\Services\ScraperAdapterFactory;
use Illuminate\Console\Command;

class ScraperTestCommand extends Command
{
    protected $signature = 'scraper:test {id : Source ID to test}';

    protected $description = 'Test a scraper source without persisting results';

    public function handle(ScraperAdapterFactory $factory): int
    {
        $source = ScraperSource::find($this->argument('id'));

        if (! $source) {
            $this->error("Source [{$this->argument('id')}] not found.");

            return 1;
        }

        $this->info("Testing source: {$source->name}");
        $this->line("  URL: {$source->base_url}");
        $this->line("  Adapter: {$source->adapter_class}");
        $this->line("  Type: {$source->source_type}");

        try {
            $adapter = $factory->make($source);

            $this->line('Fetching...');
            $raw = $adapter->fetch($source);
            $this->line('  Fetched '.strlen($raw).' bytes.');

            $this->line('Parsing...');
            $parsed = $adapter->parse($raw, $source);
            $this->line('  Parsed keys: '.implode(', ', array_keys($parsed)));

            $this->line('Normalizing...');
            $normalized = $adapter->normalize($parsed, $source);
            $confidence = $adapter->getConfidence($normalized);

            $this->newLine();
            $this->info('Results:');
            $this->line(json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->line("  Confidence: {$confidence}");

        } catch (\Throwable $e) {
            $this->error("Test failed: {$e->getMessage()}");

            return 1;
        }

        return 0;
    }
}
