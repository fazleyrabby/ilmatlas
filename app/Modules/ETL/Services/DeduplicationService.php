<?php

namespace App\Modules\ETL\Services;

use App\Modules\ETL\DTOs\InstitutionDTO;
use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Upazila;

class DeduplicationService
{
    public function findDuplicate(InstitutionDTO $dto): ?array
    {
        // Rule A: Exact EIIN / institute_code
        if ($dto->instituteCode) {
            $matched = Institute::where('institute_code', $dto->instituteCode)->first();
            if ($matched) {
                return ['institute' => $matched, 'confidence' => 100, 'rule' => 'A'];
            }
        }

        // Rule C: Fuzzy Name similarity within same District & Upazila
        if ($dto->name && $dto->district && $dto->upazila) {
            $districtModel = District::where('name', $dto->district)
                ->orWhere('slug', \Illuminate\Support\Str::slug($dto->district))
                ->first();

            $upazilaModel = Upazila::where('name', $dto->upazila)
                ->orWhere('slug', \Illuminate\Support\Str::slug($dto->upazila))
                ->first();
            
            if ($districtModel && $upazilaModel) {
                $candidates = Institute::where('district_id', $districtModel->id)
                    ->where('upazila_id', $upazilaModel->id)
                    ->get();
                
                foreach ($candidates as $candidate) {
                    similar_text(strtolower($dto->name), strtolower($candidate->name), $percent);
                    if ($percent >= 85) {
                        return ['institute' => $candidate, 'confidence' => (int) $percent, 'rule' => 'C'];
                    }
                }
            }
        }

        return null;
    }
}
