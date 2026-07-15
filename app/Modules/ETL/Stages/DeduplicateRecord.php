<?php

namespace App\Modules\ETL\Stages;

use App\Modules\ETL\DTOs\InstitutionDTO;
use App\Modules\ETL\Services\DeduplicationService;

class DeduplicateRecord
{
    protected DeduplicationService $deduplicationService;

    public function __construct(DeduplicationService $deduplicationService)
    {
        $this->deduplicationService = $deduplicationService;
    }

    public function handle(InstitutionDTO $dto, \Closure $next)
    {
        if (!$dto->isValid) {
            return $dto;
        }

        $duplicateInfo = $this->deduplicationService->findDuplicate($dto);

        if ($duplicateInfo) {
            $dto->matchedInstituteId = $duplicateInfo['institute']->id;
        }

        return $next($dto);
    }
}
