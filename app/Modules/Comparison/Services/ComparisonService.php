<?php

namespace App\Modules\Comparison\Services;

use App\Modules\Comparison\DTOs\ComparisonGroup;
use App\Modules\Comparison\DTOs\ComparisonMatrix;
use App\Modules\Comparison\DTOs\ComparisonRow;
use App\Modules\Institute\Models\Institute;
use Illuminate\Support\Facades\Cache;

class ComparisonService
{
    public function buildComparison(array $institutes): ComparisonMatrix
    {
        $groups = [
            $this->buildGeneralGroup($institutes),
            $this->buildLocationGroup($institutes),
            $this->buildCurriculumGroup($institutes),
            $this->buildFeeGroup($institutes),
            $this->buildFacilitiesGroup($institutes),
            $this->buildContactGroup($institutes),
        ];

        $groups = array_values(array_filter($groups));

        return new ComparisonMatrix(
            institutes: $institutes,
            groups: $groups,
        );
    }

    public function getComparison(array $uuids): ComparisonMatrix
    {
        sort($uuids);
        $hash = hash('sha256', implode(',', $uuids));
        $cacheKey = "comparison:{$hash}:matrix";

        return Cache::remember($cacheKey, 86400, function () use ($uuids) {
            $institutes = Institute::published()
                ->whereIn('uuid', $uuids)
                ->with([
                    'type', 'primaryCategory', 'division', 'district', 'upazila',
                    'curriculums', 'boards', 'programs',
                    'fees' => fn ($q) => $q->where('is_published', true),
                    'feeType',
                    'facilities',
                    'contacts',
                    'socialLinks',
                    'admissionCirculars' => fn ($q) => $q->where('admission_status', 'open'),
                ])
                ->get()
                ->keyBy('uuid');

            $ordered = array_map(fn ($uuid) => $institutes[$uuid] ?? null, $uuids);
            $ordered = array_values(array_filter($ordered));

            return $this->buildComparison($ordered);
        });
    }

    public function generateComparisonSlug(array $institutes): string
    {
        $slugs = array_map(fn ($i) => $i->slug, $institutes);
        return implode('-vs-', $slugs);
    }

    public function parseSlug(string $slug): array
    {
        return explode('-vs-', $slug);
    }

    private function buildGeneralGroup(array $institutes): ?ComparisonGroup
    {
        $rows = [
            new ComparisonRow('Institute Type', 'type', array_map(fn ($i) => $i->type?->name ?? 'N/A', $institutes)),
            new ComparisonRow('Established', 'established', array_map(fn ($i) => $i->established_year ?? 'N/A', $institutes)),
            new ComparisonRow('Gender', 'gender', array_map(fn ($i) => ucfirst(str_replace('_', ' ', $i->gender ?? 'N/A')), $institutes)),
            new ComparisonRow('Religious Orientation', 'orientation', array_map(fn ($i) => ucfirst(str_replace('_', ' ', $i->religious_orientation ?? 'General')), $institutes)),
            new ComparisonRow('Methodology', 'methodology', array_map(fn ($i) => ucfirst($i->methodology ?? 'N/A'), $institutes)),
        ];

        return new ComparisonGroup('General', 'general', $this->markIdentical($rows));
    }

    private function buildLocationGroup(array $institutes): ?ComparisonGroup
    {
        $rows = [
            new ComparisonRow('Division', 'division', array_map(fn ($i) => $i->division?->name ?? 'N/A', $institutes)),
            new ComparisonRow('District', 'district', array_map(fn ($i) => $i->district?->name ?? 'N/A', $institutes)),
            new ComparisonRow('Upazila', 'upazila', array_map(fn ($i) => $i->upazila?->name ?? 'N/A', $institutes)),
        ];

        return new ComparisonGroup('Location', 'location', $this->markIdentical($rows));
    }

    private function buildCurriculumGroup(array $institutes): ?ComparisonGroup
    {
        $rows = [
            new ComparisonRow('Curriculum', 'curriculum', array_map(
                fn ($i) => $i->curriculums->pluck('name')->implode(', ') ?: 'N/A',
                $institutes
            )),
            new ComparisonRow('Education Board', 'board', array_map(
                fn ($i) => $i->boards->pluck('name')->implode(', ') ?: 'N/A',
                $institutes
            )),
            new ComparisonRow('Programs', 'programs', array_map(
                fn ($i) => $i->programs->pluck('name')->implode(', ') ?: 'N/A',
                $institutes
            )),
        ];

        return new ComparisonGroup('Curriculum & Board', 'curriculum', $this->markIdentical($rows));
    }

    private function buildFeeGroup(array $institutes): ?ComparisonGroup
    {
        $rows = [
            new ComparisonRow('Estimated Monthly Fee', 'estimated_monthly_fee', array_map(
                fn ($i) => $i->estimated_monthly_fee > 0 ? '৳ '.number_format($i->estimated_monthly_fee, 0) : 'N/A',
                $institutes
            )),
        ];

        return new ComparisonGroup('Fees', 'fees', $this->markIdentical($rows));
    }

    private function buildFacilitiesGroup(array $institutes): ?ComparisonGroup
    {
        $rows = [
            new ComparisonRow('Facilities', 'facilities', array_map(
                fn ($i) => $i->facilities->pluck('name')->implode(', ') ?: 'N/A',
                $institutes
            )),
        ];

        return new ComparisonGroup('Facilities', 'facilities', $this->markIdentical($rows));
    }

    private function buildContactGroup(array $institutes): ?ComparisonGroup
    {
        $rows = [
            new ComparisonRow('Phone', 'phone', array_map(
                fn ($i) => $i->contacts->first()?->phone ?? 'N/A',
                $institutes
            )),
            new ComparisonRow('Email', 'email', array_map(
                fn ($i) => $i->contacts->first()?->email ?? 'N/A',
                $institutes
            )),
            new ComparisonRow('Address', 'address', array_map(
                fn ($i) => $i->full_address ?? 'N/A',
                $institutes
            )),
        ];

        return new ComparisonGroup('Contact', 'contact', $this->markIdentical($rows));
    }

    private function markIdentical(array $rows): array
    {
        return array_map(function (ComparisonRow $row) {
            $unique = array_unique($row->values);
            return new ComparisonRow(
                label: $row->label,
                slug: $row->slug,
                values: $row->values,
                allIdentical: count($unique) === 1,
            );
        }, $rows);
    }
}
