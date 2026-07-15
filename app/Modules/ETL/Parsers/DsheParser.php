<?php

namespace App\Modules\ETL\Parsers;

use App\Modules\ETL\DTOs\InstitutionDTO;

class DsheParser
{
    public function parse(InstitutionDTO $dto): void
    {
        $raw = $dto->rawInstitution->json_data;

        $dto->instituteCode = $raw['eiin'] ?? $raw['EIIN'] ?? null;
        $dto->name = $raw['name'] ?? $raw['institution_name'] ?? null;
        $dto->type = $raw['type'] ?? $raw['institution_type'] ?? null;
        $dto->division = $raw['division'] ?? null;
        $dto->district = $raw['district'] ?? null;
        $dto->upazila = $raw['upazila'] ?? $raw['thana'] ?? null;
        $dto->fullAddress = $raw['address'] ?? null;
        $dto->establishedYear = isset($raw['est_year']) ? (int) $raw['est_year'] : null;
        $dto->gender = $raw['gender'] ?? 'co_educational';
        $dto->category = $raw['category'] ?? 'bangla-medium';
    }
}
