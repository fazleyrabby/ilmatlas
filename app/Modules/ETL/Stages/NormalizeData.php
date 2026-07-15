<?php

namespace App\Modules\ETL\Stages;

use App\Modules\ETL\DTOs\InstitutionDTO;
use Illuminate\Support\Str;

class NormalizeData
{
    public function handle(InstitutionDTO $dto, \Closure $next)
    {
        if (!$dto->isValid) {
            return $dto;
        }

        if ($dto->name) {
            $dto->name = Str::title(trim($dto->name));
        }

        if ($dto->shortName) {
            $dto->shortName = strtoupper(trim($dto->shortName));
        }

        if ($dto->latitude) {
            $dto->latitude = (float) $dto->latitude;
        }
        if ($dto->longitude) {
            $dto->longitude = (float) $dto->longitude;
        }

        if ($dto->division) {
            $dto->division = Str::title(trim($dto->division));
        }
        if ($dto->district) {
            $dto->district = Str::title(trim($dto->district));
        }
        if ($dto->upazila) {
            $dto->upazila = Str::title(trim($dto->upazila));
        }

        return $next($dto);
    }
}
