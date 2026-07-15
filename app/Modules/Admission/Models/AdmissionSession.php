<?php

namespace App\Modules\Admission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionSession extends Model
{
    protected $fillable = ['uuid', 'name', 'slug', 'session_start', 'session_end', 'is_active'];

    protected function casts(): array
    {
        return [
            'session_start' => 'date',
            'session_end' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function circulars(): HasMany
    {
        return $this->hasMany(AdmissionCircular::class);
    }
}
