<?php

namespace App\Modules\Scraper\Commands;

use App\Modules\Scraper\Jobs\ProcessScraperJob;
use App\Modules\Scraper\Models\ScraperSource;
use App\Modules\Scraper\Services\ChangeDetector;
use App\Modules\Scraper\Services\ConfidenceScorer;
use App\Modules\Scraper\Services\ScraperAdapterFactory;
use Illuminate\Console\Command;

class ScraperSourceCommand extends Command
{
    protected $signature = 'scraper:source {id : The source ID to run}';

    protected $description = 'Run a specific scraper source immediately';

    public function handle(): int
    {
        $source = ScraperSource::find($this->argument('id'));

        if (! $source) {
            $this->error("Source [{$this->argument('id')}] not found.");

            return 1;
        }

        $this->info("Running source: {$source->name}");

        try {
            $job = new ProcessScraperJob($source);
            $job->handle(
                app(ScraperAdapterFactory::class),
                app(ChangeDetector::class),
                app(ConfidenceScorer::class),
            );
            $this->info('Done.');
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return 1;
        }

        return 0;
    }
}
