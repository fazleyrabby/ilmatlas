<?php

namespace App\Modules\Scraper\Commands;

use App\Modules\Scraper\Models\ScraperSource;
use Illuminate\Console\Command;

class ScraperListCommand extends Command
{
    protected $signature = 'scraper:list';

    protected $description = 'List all scraper sources';

    public function handle(): int
    {
        $sources = ScraperSource::with('latestRun', 'institute')->get();

        if ($sources->isEmpty()) {
            $this->info('No scraper sources configured.');

            return 0;
        }

        $rows = $sources->map(fn ($s) => [
            $s->id,
            $s->name,
            $s->source_type,
            $s->institute?->name ?? '-',
            $s->schedule_frequency,
            $s->is_active ? 'Yes' : 'No',
            $s->last_successful_run_at?->diffForHumans() ?? 'Never',
            $s->latestRun?->status ?? '-',
        ]);

        $this->table(
            ['ID', 'Name', 'Type', 'Institute', 'Schedule', 'Active', 'Last Run', 'Status'],
            $rows,
        );

        return 0;
    }
}
