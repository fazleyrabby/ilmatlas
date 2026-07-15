<?php

namespace App\Modules\ETL\Commands;

use App\Modules\ETL\Models\RawImport;
use App\Modules\ETL\Models\RawInstitution;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportOsmCommand extends Command
{
    protected $signature = 'edu:import-osm {--limit=100 : Maximum number of elements to import}';
    protected $description = 'Fetch authentic school and madrasa data directly from OpenStreetMap Overpass API for Chittagong';

    public function handle(): int
    {
        $this->info("Querying OpenStreetMap Overpass API for Chittagong (Chattogram) district...");

        $query = '[out:json][timeout:60];(node(21.8,91.3,23.1,92.3)[\'amenity\'=\'school\'];node(21.8,91.3,23.1,92.3)[\'amenity\'=\'madrasa\'];);out center;';

        try {
            $cmd = 'curl -s -G --data-urlencode "data=' . $query . '" https://overpass-api.de/api/interpreter';
            $json = shell_exec($cmd);

            if (empty($json)) {
                $this->error("Failed to retrieve data from Overpass API.");
                return 1;
            }

            $data = json_decode($json, true);
            $elements = $data['elements'] ?? [];
            $count = count($elements);

            $this->info("Found {$count} elements in Chittagong. Processing and staging...");

            if ($count === 0) {
                return 0;
            }

            $limit = (int) $this->option('limit');
            $elements = array_slice($elements, 0, $limit);

            $import = RawImport::create([
                'source' => 'openstreetmap',
                'file_name' => 'overpass_chittagong_live',
                'status' => 'pending',
                'record_count' => count($elements),
            ]);

            $this->output->progressStart(count($elements));

            foreach ($elements as $el) {
                $tags = $el['tags'] ?? [];
                $name = $tags['name'] ?? $tags['name:en'] ?? null;

                if (!$name) {
                    $this->output->progressAdvance();
                    continue;
                }

                $lat = $el['lat'] ?? $el['center']['lat'] ?? null;
                $lon = $el['lon'] ?? $el['center']['lon'] ?? null;

                $amenity = $tags['amenity'] ?? 'school';
                $type = (str_contains(strtolower($name), 'madrasa') || $amenity === 'madrasa') ? 'madrasa' : 'school';

                // Map raw institution
                $mappedRecord = [
                    'name' => Str::title(trim($name)),
                    'eiin' => $tags['ref:eiin'] ?? $tags['eiin'] ?? null,
                    'type' => $type,
                    'division' => 'Chattogram',
                    'district' => 'Chattogram',
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'full_address' => $tags['addr:full'] ?? $tags['addr:street'] ?? null,
                    'website' => $tags['website'] ?? $tags['contact:website'] ?? null,
                    'phone' => $tags['phone'] ?? $tags['contact:phone'] ?? null,
                    'established_year' => isset($tags['start_date']) ? (int) $tags['start_date'] : null,
                ];

                RawInstitution::create([
                    'raw_import_id' => $import->id,
                    'source' => 'openstreetmap',
                    'external_id' => (string) ($el['id'] ?? Str::uuid()),
                    'json_data' => $mappedRecord,
                    'hash' => md5(serialize($mappedRecord)),
                    'status' => 'pending',
                ]);

                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            $import->update(['status' => 'completed']);

            $this->info("Successfully staged " . count($elements) . " authentic OSM records.");
            $this->info("Run `php artisan edu:process-etl --source=openstreetmap` to import them into the main database.");

        } catch (\Throwable $e) {
            $this->error("Failed to import: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
