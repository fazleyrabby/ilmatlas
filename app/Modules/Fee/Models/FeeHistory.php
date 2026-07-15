<?php

namespace App\Modules\Fee\Models;

use App\Modules\Institute\Models\Institute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeHistory extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'uuid', 'fee_structure_id', 'institute_id', 'fee_type_id',
        'previous_amount', 'new_amount', 'percentage_change',
        'effective_date', 'academic_session', 'change_reason',
        'verification_status', 'source_url', 'source_type',
        'scraper_run_id', 'changed_by', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'previous_amount' => 'decimal:2',
            'new_amount' => 'decimal:2',
            'percentage_change' => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }
}
