<?php

namespace App\Modules\ETL\Parsers;

use App\Modules\ETL\DTOs\InstitutionDTO;

class BanbeisParser
{
    public function parse(InstitutionDTO $dto): void
    {
        $raw = $dto->rawInstitution->json_data;

        $dto->instituteCode = $raw['EIIN'] ?? $raw['eiin'] ?? null;
        $dto->name = $raw['INST_NAME'] ?? $raw['name'] ?? null;
        $dto->type = $raw['INST_TYPE'] ?? $raw['type'] ?? null;
        $dto->division = $raw['DIVISION'] ?? $raw['division'] ?? null;
        $dto->district = $raw['DISTRICT'] ?? $raw['district'] ?? null;
        $dto->upazila = $raw['UPAZILA'] ?? $raw['upazila'] ?? null;
        $dto->board = $raw['BOARD'] ?? $raw['board'] ?? null;
        
        $gender = strtolower($raw['GENDER'] ?? $raw['gender'] ?? '');
        if (str_contains($gender, 'boy') || $gender === 'male') {
            $dto->gender = 'boys';
        } elseif (str_contains($gender, 'girl') || $gender === 'female') {
            $dto->gender = 'girls';
        } else {
            $dto->gender = 'co_educational';
        }

        $mgmt = strtolower($raw['MANAGEMENT'] ?? $raw['management'] ?? '');
        if (str_contains($mgmt, 'gov')) {
            $dto->category = 'government';
        } else {
            $dto->category = 'private';
        }

        $dto->establishedYear = isset($raw['ESTABLISHED']) ? (int) $raw['ESTABLISHED'] : null;
    }
}
