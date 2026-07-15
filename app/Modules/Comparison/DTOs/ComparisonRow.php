<?php

namespace App\Modules\Comparison\DTOs;

class ComparisonRow
{
    public function __construct(
        public readonly string $label,
        public readonly string $slug,
        public readonly array $values,
        public readonly bool $allIdentical = false,
    ) {}
}
