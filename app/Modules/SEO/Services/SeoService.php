<?php

namespace App\Modules\SEO\Services;

use App\Modules\Institute\Models\Institute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SeoService
{
    public function forInstitute(Institute $institute): array
    {
        $overrides = $this->getOverrides($institute);

        $typeName = $institute->type?->name ?? '';
        $areaName = $institute->area?->name ?? '';
        $districtName = $institute->district?->name ?? '';
        $locationName = $areaName ?: $districtName;
        $description = $institute->description ?? '';

        return [
            'meta_title' => $overrides['meta_title'] ?? "{$institute->name} — {$typeName} in {$locationName} | EduBase",
            'meta_description' => $overrides['meta_description'] ?? Str::limit("{$institute->name} is a {$typeName} in {$locationName}. {$description}", 160),
            'meta_keywords' => $overrides['meta_keywords'] ?? "{$institute->name}, {$typeName}, {$districtName}",
            'og_title' => $overrides['og_title'] ?? "{$institute->name} | EduBase",
            'og_description' => $overrides['og_description'] ?? Str::limit("{$institute->name} — {$typeName} in {$districtName}", 200),
            'og_type' => $overrides['og_type'] ?? 'website',
            'og_image' => $overrides['og_image'] ?? $institute->logo_url,
            'canonical_url' => $overrides['canonical_url'] ?? route('institutes.show', $institute),
            'noindex' => $overrides['noindex'] ?? false,
            'schema_type' => $overrides['schema_type'] ?? 'EducationalOrganization',
        ];
    }

    public function forLocation(string $type, string $name, int $count, string $extra = ''): array
    {
        $title = $extra
            ? "{$extra} in {$name} | EduBase"
            : "{$type} in {$name} | EduBase";

        $description = $extra
            ? "Find {$count} {$extra} in {$name}. Compare fees, curriculum, facilities, and admission status."
            : "Find {$count} {$type} in {$name}. Compare fees, curriculum, facilities, and admission status.";

        return [
            'meta_title' => $title,
            'meta_description' => $description,
            'canonical_url' => request()->url(),
            'schema_type' => 'WebPage',
        ];
    }

    public function forSearch(string $query, int $count): array
    {
        return [
            'meta_title' => "Search results for \"{$query}\" | EduBase",
            'meta_description' => "Found {$count} institutes matching \"{$query}\". Compare fees, curriculum, and admission information.",
            'canonical_url' => $query ? null : request()->url(),
            'noindex' => true,
            'schema_type' => 'SearchResultsPage',
        ];
    }

    public function forStatic(string $title, string $description): array
    {
        return [
            'meta_title' => "{$title} | EduBase",
            'meta_description' => $description,
            'canonical_url' => request()->url(),
            'schema_type' => 'WebPage',
        ];
    }

    public function forPSEO(string $locationName, string $typeSlug, string $typeLabel, int $count): array
    {
        return [
            'meta_title' => "{$typeLabel} in {$locationName} | EduBase",
            'meta_description' => "Find {$count} {$typeLabel} in {$locationName}. Compare fees, curriculum, facilities, and admission information.",
            'canonical_url' => request()->url(),
            'schema_type' => 'WebPage',
        ];
    }

    private function getOverrides(Model $entity): array
    {
        if (! method_exists($entity, 'seoMetadata')) {
            return [];
        }

        $meta = $entity->seoMetadata;

        if (! $meta) {
            return [];
        }

        return [
            'meta_title' => $meta->meta_title,
            'meta_description' => $meta->meta_description,
            'meta_keywords' => $meta->meta_keywords,
            'og_title' => $meta->og_title,
            'og_description' => $meta->og_description,
            'og_image' => $meta->og_image,
            'og_type' => $meta->og_type,
            'canonical_url' => $meta->canonical_url,
            'noindex' => $meta->noindex,
            'schema_type' => $meta->schema_type,
        ];
    }
}
