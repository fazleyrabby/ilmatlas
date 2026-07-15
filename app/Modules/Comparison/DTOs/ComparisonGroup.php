<?php

namespace App\Modules\Comparison\DTOs;

class ComparisonGroup
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly array $rows,
    ) {}
}
