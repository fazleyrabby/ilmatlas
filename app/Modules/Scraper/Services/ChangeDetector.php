<?php

namespace App\Modules\Scraper\Services;

use App\Modules\Fee\Models\FeeStructure;
use App\Modules\Institute\Models\Institute;

class ChangeDetector
{
    public function detectFeeChanges(Institute $institute, array $scrapedFees): array
    {
        $changes = [];

        foreach ($scrapedFees as $scraped) {
            $existing = FeeStructure::where('institute_id', $institute->id)
                ->where('fee_type_id', $scraped['fee_type_id'] ?? 0)
                ->where('academic_session', $scraped['academic_session'] ?? date('Y'))
                ->where('is_published', true)
                ->first();

            if (! $existing) {
                $changes[] = [
                    'type' => 'new',
                    'field' => 'fee',
                    'scraped' => $scraped,
                    'existing' => null,
                    'deviation_percent' => null,
                ];

                continue;
            }

            $oldAmount = (float) $existing->amount;
            $newAmount = (float) ($scraped['amount'] ?? 0);
            $deviation = $oldAmount > 0
                ? round((($newAmount - $oldAmount) / $oldAmount) * 100, 2)
                : null;

            if (abs($deviation ?? 0) > 0.01) {
                $changes[] = [
                    'type' => 'changed',
                    'field' => 'fee',
                    'scraped' => $scraped,
                    'existing' => $existing->toArray(),
                    'deviation_percent' => $deviation,
                ];
            }
        }

        return $changes;
    }

    public function hasSignificantChanges(array $changes, float $threshold = 10.0): bool
    {
        foreach ($changes as $change) {
            if ($change['type'] === 'new') {
                return true;
            }
            if ($change['type'] === 'changed' && abs($change['deviation_percent'] ?? 0) >= $threshold) {
                return true;
            }
        }

        return false;
    }
}
