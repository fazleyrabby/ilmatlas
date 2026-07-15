<?php

namespace App\Modules\Location\Models;

use App\Modules\Institute\Models\Institute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $fillable = [
        'uuid', 'country_id', 'name', 'slug', 'bn_name',
        'latitude', 'longitude', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function institutes(): HasMany
    {
        return $this->hasMany(Institute::class);
    }

    public function publishedInstitutes(): HasMany
    {
        return $this->hasMany(Institute::class)->where('status', 'published');
    }
}
