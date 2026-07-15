<?php

namespace App\Modules\SEO\Commands;

use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\District;
use App\Modules\Taxonomy\Models\InstituteType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GeneratePSEO extends Command
{
    protected $signature = 'seo:generate-pseo';

    protected $description = 'Pre-cache programmatic SEO page data for all type-district combinations';

    public function handle(): int
    {
        $types = InstituteType::select('id', 'slug', 'name')->get();
        $districts = District::select('id', 'slug', 'name')->get();

        $bar = $this->output->createProgressBar($types->count() * $districts->count());
        $bar->start();

        foreach ($types as $type) {
            foreach ($districts as $district) {
                $cacheKey = "pseo:{$type->slug}:{$district->slug}";
                $count = Institute::where('institute_type_id', $type->id)
                    ->whereHas('district', fn ($q) => $q->where('id', $district->id))
                    ->count();

                Cache::forever($cacheKey, [
                    'type' => $type,
                    'district' => $district,
                    'count' => $count,
                ]);

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('PSEO data cached for '.($types->count() * $districts->count()).' combinations.');

        return Command::SUCCESS;
    }
}
