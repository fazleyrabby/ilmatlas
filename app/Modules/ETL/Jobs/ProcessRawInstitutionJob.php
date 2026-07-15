<?php

namespace App\Modules\ETL\Jobs;

use App\Modules\ETL\DTOs\InstitutionDTO;
use App\Modules\ETL\Models\RawInstitution;
use App\Modules\ETL\Stages\DeduplicateRecord;
use App\Modules\ETL\Stages\NormalizeData;
use App\Modules\ETL\Stages\ParseRawAttributes;
use App\Modules\ETL\Stages\PersistToProduction;
use App\Modules\ETL\Stages\ValidateAttributes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Pipeline;

class ProcessRawInstitutionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected RawInstitution $rawInstitution;

    public function __construct(RawInstitution $rawInstitution)
    {
        $this->rawInstitution = $rawInstitution;
    }

    public function handle(): void
    {
        $dto = new InstitutionDTO($this->rawInstitution);

        Pipeline::send($dto)
            ->through([
                ParseRawAttributes::class,
                NormalizeData::class,
                ValidateAttributes::class,
                DeduplicateRecord::class,
                PersistToProduction::class,
            ])
            ->then(fn ($dto) => $dto->markProcessed());
    }
}
