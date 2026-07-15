<?php

namespace App\Modules\Search\Services;

use App\Modules\Institute\Models\Institute;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

class SearchService
{
    /**
     * Search institutes. Uses Scout/Meilisearch when available,
     * otherwise falls back to a plain database search.
     */
    public function search(string $query, array $filters = [], int $perPage = 20): ScoutBuilder|EloquentBuilder
    {
        if ($this->scoutUsable()) {
            try {
                $search = Institute::search($query);

                foreach ($filters as $field => $value) {
                    if ($value !== null && $value !== '') {
                        $search->where($field, $value);
                    }
                }

                return $search;
            } catch (\Throwable) {
                // Meilisearch unreachable — fall through to database search.
            }
        }

        return $this->databaseSearch($query, $filters);
    }

    public function autocomplete(string $query, int $limit = 8): array
    {
        if ($this->scoutUsable()) {
            try {
                return $this->mapResults(Institute::search($query)->take($limit)->get());
            } catch (\Throwable) {
                // fall through
            }
        }

        return $this->mapResults(
            $this->databaseSearch($query, [])->limit($limit)->get()
        );
    }

    private function databaseSearch(string $query, array $filters): EloquentBuilder
    {
        $type = $filters['type'] ?? $filters['type_slug'] ?? null;
        $district = $filters['district'] ?? null;
        $gender = $filters['gender'] ?? null;

        return Institute::query()
            ->published()
            ->with(['type', 'district', 'upazila'])
            ->when($query !== '', function (EloquentBuilder $q) use ($query) {
                $q->where(function (EloquentBuilder $sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('short_name', 'like', "%{$query}%")
                        ->orWhere('institute_code', 'like', "%{$query}%");
                });
            })
            ->when($type, fn (EloquentBuilder $q, $slug) => $q->whereHas('type', fn ($t) => $t->where('slug', $slug)))
            ->when($district, fn (EloquentBuilder $q, $slug) => $q->whereHas('district', fn ($d) => $d->where('slug', $slug)))
            ->when($gender, fn (EloquentBuilder $q, $g) => $q->where('gender', $g))
            ->orderBy('name');
    }

    private function mapResults($institutes): array
    {
        return $institutes->map(fn (Institute $institute) => [
            'uuid' => $institute->uuid,
            'name' => $institute->name,
            'slug' => $institute->slug,
            'type' => $institute->type?->name,
            'district' => $institute->district?->name,
            'fee' => $institute->estimated_monthly_fee,
            'logo_url' => $institute->logo_url,
        ])->toArray();
    }

    private function scoutUsable(): bool
    {
        if (config('scout.driver') === 'meilisearch') {
            return class_exists(\Meilisearch\Client::class);
        }

        return true;
    }
}
