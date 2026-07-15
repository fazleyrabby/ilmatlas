<?php

namespace App\Modules\Fee\Services;

use App\Modules\Fee\Models\FeeStructure;

class FeeCalculatorService
{
    private const FREQUENCY_MULTIPLIERS = [
        'one_time' => 0,
        'monthly' => 1,
        'quarterly' => 1 / 3,
        'half_yearly' => 1 / 6,
        'yearly' => 1 / 12,
        'per_unit' => null,
    ];

    public function estimateMonthlyFee(int $instituteId): ?float
    {
        $fees = FeeStructure::where('institute_id', $instituteId)
            ->where('moderation_status', 'approved')
            ->where('is_published', true)
            ->get();

        if ($fees->isEmpty()) {
            return null;
        }

        $monthlyTotal = 0;
        foreach ($fees as $fee) {
            $monthly = $this->normalizeToMonthly($fee->amount, $fee->frequency);
            if ($monthly !== null) {
                $monthlyTotal += $monthly;
            }
        }

        return round($monthlyTotal, 2);
    }

    public function normalizeToMonthly(float $amount, string $frequency): ?float
    {
        $multiplier = self::FREQUENCY_MULTIPLIERS[$frequency] ?? null;
        if ($multiplier === null) {
            return null;
        }

        if ($multiplier === 0) {
            return 0;
        }

        return round($amount * $multiplier, 2);
    }

    public function calculatePercentageChange(?float $previous, float $current): ?float
    {
        if ($previous === null || $previous == 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
