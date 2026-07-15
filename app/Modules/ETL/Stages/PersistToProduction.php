<?php

namespace App\Modules\ETL\Stages;

use App\Modules\ETL\DTOs\InstitutionDTO;
use App\Modules\ETL\Models\InstitutionSource;
use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Division;
use App\Modules\Location\Models\Upazila;
use App\Modules\Taxonomy\Models\InstituteType;
use Illuminate\Support\Str;

class PersistToProduction
{
    public function handle(InstitutionDTO $dto, \Closure $next)
    {
        if (!$dto->isValid) {
            return $dto;
        }

        $division = Division::where('name', $dto->division)
            ->orWhere('slug', Str::slug($dto->division))
            ->first();
            
        if (!$division) {
            $dto->markFailed("Division not found: {$dto->division}");
            return $dto;
        }

        $district = District::where('division_id', $division->id)
            ->where(fn ($query) => $query->where('name', $dto->district)->orWhere('slug', Str::slug($dto->district)))
            ->first();
            
        if (!$district) {
            $dto->markFailed("District not found: {$dto->district} under Division {$dto->division}");
            return $dto;
        }

        $upazila = null;
        if ($dto->upazila) {
            $upazila = Upazila::where('district_id', $district->id)
                ->where(fn ($query) => $query->where('name', $dto->upazila)->orWhere('slug', Str::slug($dto->upazila)))
                ->first();
        }

        $type = null;
        if ($dto->type) {
            $type = InstituteType::where('name', $dto->type)
                ->orWhere('slug', Str::slug($dto->type))
                ->first();
        }
        if (!$type) {
            $type = InstituteType::where('slug', 'school')->first() ?? InstituteType::first();
        }

        $slug = $dto->name ? Str::slug($dto->name) : 'institute';
        
        $data = [
            'uuid' => (string) Str::uuid(),
            'name' => $dto->name,
            'short_name' => $dto->shortName,
            'institute_code' => $dto->instituteCode,
            'institute_type_id' => $type?->id,
            'country_id' => $division->country_id,
            'division_id' => $division->id,
            'district_id' => $district->id,
            'upazila_id' => $upazila?->id,
            'gender' => $dto->gender ?? 'co_educational',
            'established_year' => $dto->establishedYear,
            'full_address' => $dto->fullAddress,
            'latitude' => $dto->latitude,
            'longitude' => $dto->longitude,
            'status' => 'published',
            'published_at' => now(),
            'verification_status' => $dto->verificationStatus,
        ];

        if ($dto->matchedInstituteId) {
            $institute = Institute::findOrFail($dto->matchedInstituteId);
            unset($data['uuid']);
            $institute->update($data);
        } else {
            $existingCount = Institute::where('slug', 'like', "{$slug}%")->count();
            if ($existingCount > 0) {
                $slug = "{$slug}-" . ($existingCount + 1);
            }
            $data['slug'] = $slug;
            $institute = Institute::create($data);
        }

        $confidence = 100;
        $fields = ['name', 'institute_code', 'institute_type_id', 'division_id', 'district_id', 'upazila_id'];
        
        foreach ($fields as $field) {
            InstitutionSource::updateOrCreate(
                [
                    'institute_id' => $institute->id,
                    'field_name' => $field,
                    'source_name' => $dto->rawInstitution->source,
                ],
                [
                    'confidence_score' => $confidence,
                ]
            );
        }

        return $next($dto);
    }
}
