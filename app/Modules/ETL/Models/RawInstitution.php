<?php

namespace App\Modules\ETL\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawInstitution extends Model
{
    protected $fillable = [
        'raw_import_id',
        'source',
        'external_id',
        'json_data',
        'hash',
        'status',
    ];

    protected $casts = [
        'json_data' => 'array',
    ];

    public function rawImport(): BelongsTo
    {
        return $this->belongsTo(RawImport::class, 'raw_import_id');
    }
}
