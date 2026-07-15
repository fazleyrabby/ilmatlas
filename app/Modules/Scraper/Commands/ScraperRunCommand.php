<?php

namespace App\Modules\Scraper\Commands;

use App\Modules\Scraper\Jobs\ProcessScraperJob;
use App\Modules\Scraper\Models\ScraperSource;
use Illuminate\Console\Command;

class ScraperRunCommand extends Command
{
    protected $signature = 'scraper:run
        {--frequency= : Run sources with this frequency (hourly, daily, weekly, monthly)}
        {--source= : Run a specific source by ID}
        {--dry-run : Show what would be run without executing}';

    protected $description = 'Run scraper sources';

    public function handle(): int
    {
        $query = ScraperSource::active();

        if ($sourceId = $this->option('source')) {
            $query->where('id', $sourceId);
        } elseif ($frequency = $this->option('frequency')) {
            $query->byFrequency($frequency);
        } else {
            $query->byFrequency('hourly');
        }

        $sources = $query->get();

        if ($sources->isEmpty()) {
            $this->info('No sources to run.');

            return 0;
        }

        $this->info("Found {$sources->count()} source(s) to process.");

        foreach ($sources as $source) {
            $this->line("  [{$source->id}] {$source->name}");

            if ($this->option('dry-run')) {
                continue;
            }

            ProcessScraperJob::dispatch($source);
            $this->line('    -> Dispatched.');
        }

        return 0;
    }
}
