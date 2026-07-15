<?php

namespace App\Modules\ETL\Stages;

use App\Modules\ETL\DTOs\InstitutionDTO;
use App\Modules\ETL\Parsers\BanbeisParser;
use App\Modules\ETL\Parsers\DsheParser;
use App\Modules\ETL\Parsers\DataGovParser;

use App\Modules\ETL\Parsers\OsmParser;

class ParseRawAttributes
{
    public function handle(InstitutionDTO $dto, \Closure $next)
    {
        $source = strtolower($dto->rawInstitution->source);

        $parser = match ($source) {
            'banbeis' => new BanbeisParser(),
            'openstreetmap' => new OsmParser(),
            'dshe' => new DsheParser(),
            'datagov', 'data.gov.bd' => new DataGovParser(),
            default => null,
        };

        if ($parser) {
            $parser->parse($dto);
        } else {
            $dto->markFailed("No parser registered for source: {$source}");
            return $dto;
        }

        return $next($dto);
    }
}
