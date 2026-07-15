<?php

namespace App\Modules\ETL\DTOs;

use App\Modules\ETL\Models\RawInstitution;

class InstitutionDTO
{
    public RawInstitution $rawInstitution;
    public ?string $name = null;
    public ?string $shortName = null;
    public ?string $instituteCode = null; // EIIN
    public ?string $type = null; // school, madrasa, college, etc.
    public ?string $category = null; // bangla-medium, english-medium, etc.
    public ?string $curriculum = null;
    public ?string $board = null;
    public ?string $gender = null; // boys, girls, co_educational
    public ?string $religiousOrientation = null;
    public ?int $establishedYear = null;
    public ?string $division = null;
    public ?string $district = null;
    public ?string $upazila = null;
    public ?string $fullAddress = null;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?string $sourceUrl = null;
    public ?string $verificationStatus = 'estimated';
    public array $programs = [];
    public array $facilities = [];
    public array $contacts = [];
    public array $fees = [];
    
    // Processed state
    public bool $isValid = true;
    public array $errors = [];
    public ?int $matchedInstituteId = null;

    public function __construct(RawInstitution $rawInstitution)
    {
        $this->rawInstitution = $rawInstitution;
    }

    public function markProcessed(): void
    {
        $this->rawInstitution->update(['status' => 'processed']);
    }

    public function markFailed(string $error): void
    {
        $this->isValid = false;
        $this->errors[] = $error;
        $this->rawInstitution->update(['status' => 'failed']);
    }
}
