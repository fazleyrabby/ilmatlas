<?php

namespace App\Modules\ETL\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawImport extends Model
{
    protected $fillable = [
        'source',
        'file_name',
        'status',
        'record_count',
    ];

    public function rawInstitutions(): HasMany
    {
        return $this->hasMany(RawInstitution::class, 'raw_import_id');
    }
}
