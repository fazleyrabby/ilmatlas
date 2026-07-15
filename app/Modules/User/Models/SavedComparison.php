<?php

namespace App\Modules\User\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedComparison extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'institute_ids',
    ];

    protected function casts(): array
    {
        return [
            'institute_ids' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
