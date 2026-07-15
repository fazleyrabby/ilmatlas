<?php

namespace App\Modules\ETL\Commands;

use App\Modules\Institute\Models\Institute;
use Illuminate\Console\Command;

class DeduplicateCommand extends Command
{
    protected $signature = 'edu:deduplicate {--fuzzy} {--threshold=0.85}';
    protected $description = 'Deduplicate production institute records';

    public function handle(): void
    {
        $fuzzy = $this->option('fuzzy');
        $threshold = (float) $this->option('threshold') * 100;

        $this->info("Scanning institutes for duplicates...");

        $institutes = Institute::all();
        $count = $institutes->count();
        $duplicatesCount = 0;

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $inst1 = $institutes[$i];
                $inst2 = $institutes[$j];

                if ($inst1->institute_code && $inst2->institute_code && $inst1->institute_code === $inst2->institute_code) {
                    $this->warn("Exact duplicate found by code: {$inst1->name} (ID: {$inst1->id}) and {$inst2->name} (ID: {$inst2->id})");
                    $duplicatesCount++;
                    continue;
                }

                if ($fuzzy && $inst1->district_id === $inst2->district_id && $inst1->upazila_id === $inst2->upazila_id) {
                    similar_text(strtolower($inst1->name), strtolower($inst2->name), $percent);
                    if ($percent >= $threshold) {
                        $this->warn("Fuzzy duplicate found ({$percent}%): {$inst1->name} (ID: {$inst1->id}) and {$inst2->name} (ID: {$inst2->id})");
                        $duplicatesCount++;
                    }
                }
            }
        }

        $this->info("Scan complete. Found {$duplicatesCount} potential duplicates.");
    }
}
