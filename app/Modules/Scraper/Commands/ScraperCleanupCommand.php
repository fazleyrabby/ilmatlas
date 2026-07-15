<?php

namespace App\Modules\Scraper\Commands;

use App\Modules\Scraper\Models\ScraperLog;
use App\Modules\Scraper\Models\ScraperRun;
use Illuminate\Console\Command;

class ScraperCleanupCommand extends Command
{
    protected $signature = 'scraper:cleanup {--older-than=90 : Delete runs older than this many days}';

    protected $description = 'Remove old scraper runs and logs';

    public function handle(): int
    {
        $days = (int) $this->option('older-than');
        $cutoff = now()->subDays($days);

        $oldRunIds = ScraperRun::where('created_at', '<', $cutoff)->pluck('id');

        $logCount = ScraperLog::whereIn('scraper_run_id', $oldRunIds)->delete();
        $this->info("Deleted {$logCount} log entries.");

        $runCount = ScraperRun::whereIn('id', $oldRunIds)->delete();
        $this->info("Deleted {$runCount} runs.");

        return 0;
    }
}
