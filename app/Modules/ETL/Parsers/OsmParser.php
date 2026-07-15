<?php

namespace App\Modules\ETL\Parsers;

use App\Modules\ETL\DTOs\InstitutionDTO;

class OsmParser
{
    public function parse(InstitutionDTO $dto): void
    {
        $raw = $dto->rawInstitution->json_data;

        $dto->name = $raw['name'] ?? null;
        $dto->instituteCode = $raw['eiin'] ?? null;
        $dto->type = $raw['type'] ?? 'school';
        $dto->category = $raw['category'] ?? 'bangla-medium';
        $dto->division = $raw['division'] ?? 'Chattogram';
        $dto->district = $raw['district'] ?? 'Chattogram';
        $dto->upazila = $raw['upazila'] ?? null;
        
        $dto->latitude = isset($raw['latitude']) ? (float) $raw['latitude'] : null;
        $dto->longitude = isset($raw['longitude']) ? (float) $raw['longitude'] : null;
        $dto->fullAddress = $raw['full_address'] ?? null;
        $dto->sourceUrl = $raw['website'] ?? null;
        $dto->establishedYear = isset($raw['established_year']) ? (int) $raw['established_year'] : null;
        
        $dto->gender = $raw['gender'] ?? 'co_educational';

        if ($raw['phone'] ?? null) {
            $dto->contacts[] = [
                'type' => 'phone',
                'value' => $raw['phone']
            ];
        }
        if ($raw['website'] ?? null) {
            $dto->contacts[] = [
                'type' => 'email',
                'value' => 'info@' . \Illuminate\Support\Str::slug($dto->name) . '.edu.bd'
            ];
        }
    }
}
