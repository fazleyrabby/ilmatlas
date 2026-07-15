<?php

namespace App\Modules\ETL\Parsers;

use App\Modules\ETL\DTOs\InstitutionDTO;

class DataGovParser
{
    public function parse(InstitutionDTO $dto): void
    {
        $raw = $dto->rawInstitution->json_data;

        $dto->instituteCode = $raw['eiin'] ?? $raw['code'] ?? null;
        $dto->name = $raw['name'] ?? $raw['institute_name'] ?? null;
        $dto->type = $raw['type'] ?? null;
        $dto->division = $raw['division'] ?? null;
        $dto->district = $raw['district'] ?? null;
        $dto->upazila = $raw['upazila'] ?? null;
        $dto->gender = $raw['gender'] ?? 'co_educational';
        $dto->category = $raw['category'] ?? 'bangla-medium';
    }
}
