<?php

namespace App\Modules\Comparison\DTOs;

use Carbon\Carbon;

class ComparisonMatrix
{
    public function __construct(
        public readonly array $institutes,
        public readonly array $groups,
        public readonly Carbon $generatedAt = new Carbon,
    ) {}

    public function onlyDifferences(): self
    {
        $filtered = [];
        foreach ($this->groups as $group) {
            $rows = array_filter($group->rows, fn (ComparisonRow $r) => ! $r->allIdentical);
            if (! empty($rows)) {
                $filtered[] = new ComparisonGroup($group->name, $group->slug, array_values($rows));
            }
        }

        return new self($this->institutes, $filtered, $this->generatedAt);
    }
}
