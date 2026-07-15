<?php

namespace App\Modules\ETL\Commands;

use App\Modules\ETL\Models\RawImport;
use App\Modules\ETL\Models\RawInstitution;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class IngestCommand extends Command
{
    protected $signature = 'edu:ingest {source} {file}';
    protected $description = 'Ingest bulk datasets (JSON format supported natively) into staging area';

    public function handle(): void
    {
        $source = $this->argument('source');
        $filePath = $this->argument('file');

        if (!File::exists($filePath)) {
            $this->error("File does not exist: {$filePath}");
            return;
        }

        $this->info("Ingesting file: {$filePath} for source: {$source}");

        $content = File::get($filePath);
        $records = json_decode($content, true);

        if (!is_array($records)) {
            $this->error("Invalid JSON content or format.");
            return;
        }

        if (isset($records['institutes'])) {
            $records = $records['institutes'];
        }

        $import = RawImport::create([
            'source' => $source,
            'file_name' => basename($filePath),
            'status' => 'pending',
            'record_count' => count($records),
        ]);

        $this->output->progressStart(count($records));

        foreach ($records as $record) {
            $externalId = $record['EIIN'] ?? $record['eiin'] ?? $record['id'] ?? null;
            $hash = md5(serialize($record));

            RawInstitution::create([
                'raw_import_id' => $import->id,
                'source' => $source,
                'external_id' => $externalId,
                'json_data' => $record,
                'hash' => $hash,
                'status' => 'pending',
            ]);

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $import->update(['status' => 'completed']);
        
        $this->info("Successfully ingested " . count($records) . " records for staging.");
    }
}
