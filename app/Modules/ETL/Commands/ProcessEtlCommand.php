<?php

namespace App\Modules\ETL\Commands;

use App\Modules\ETL\Models\RawInstitution;
use App\Modules\ETL\Jobs\ProcessRawInstitutionJob;
use Illuminate\Console\Command;

class ProcessEtlCommand extends Command
{
    protected $signature = 'edu:process-etl {--source=} {--chunk=500}';
    protected $description = 'Process raw staged records through the ETL pipeline';

    public function handle(): void
    {
        $source = $this->option('source');
        $chunk = (int) $this->option('chunk');

        $query = RawInstitution::where('status', 'pending');
        if ($source) {
            $query->where('source', $source);
        }

        $count = $query->count();
        if ($count === 0) {
            $this->info("No pending raw records to process.");
            return;
        }

        $this->info("Processing {$count} staged raw records...");

        $this->output->progressStart($count);

        $query->chunk($chunk, function ($records) {
            foreach ($records as $record) {
                (new ProcessRawInstitutionJob($record))->handle();
                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();
        $this->info("ETL processing complete!");
    }
}
