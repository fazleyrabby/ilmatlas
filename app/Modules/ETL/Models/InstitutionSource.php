<?php

namespace App\Modules\ETL\Models;

use App\Modules\Institute\Models\Institute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionSource extends Model
{
    protected $fillable = [
        'institute_id',
        'field_name',
        'source_name',
        'confidence_score',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }
}
