<?php

namespace App\Modules\Institute\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteClaim extends Model
{
    protected $fillable = [
        'user_id',
        'institute_id',
        'proof_url',
        'notes',
        'status',
        'moderated_by',
        'moderated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}
