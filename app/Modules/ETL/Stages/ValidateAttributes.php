<?php

namespace App\Modules\ETL\Stages;

use App\Modules\ETL\DTOs\InstitutionDTO;

class ValidateAttributes
{
    public function handle(InstitutionDTO $dto, \Closure $next)
    {
        if (!$dto->isValid) {
            return $dto;
        }

        if (empty($dto->name)) {
            $dto->markFailed("Missing institution name.");
            return $dto;
        }

        if (empty($dto->division)) {
            $dto->markFailed("Missing division.");
            return $dto;
        }

        if (empty($dto->district)) {
            $dto->markFailed("Missing district.");
            return $dto;
        }

        return $next($dto);
    }
}
