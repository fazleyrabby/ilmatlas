<?php

namespace App\Modules\Scraper\Models;

use App\Modules\Institute\Models\Institute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScraperSource extends Model
{
    protected $fillable = [
        'uuid', 'institute_id', 'name', 'source_type', 'adapter_class',
        'base_url', 'config', 'trust_level', 'schedule_frequency',
        'last_successful_run_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'json',
            'is_active' => 'boolean',
            'last_successful_run_at' => 'datetime',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ScraperRun::class);
    }

    public function latestRun(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ScraperRun::class)->latestOfMany();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByFrequency($query, string $frequency)
    {
        return $query->where('schedule_frequency', $frequency);
    }
}
