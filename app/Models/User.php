<?php

namespace App\Models;

use App\Modules\Institute\Models\Institute;
use App\Modules\Institute\Models\InstituteClaim;
use App\Modules\User\Models\SavedComparison;
use App\Modules\User\Models\UserAlert;
use App\Modules\User\Models\UserFavorite;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    public function savedComparisons(): HasMany
    {
        return $this->hasMany(SavedComparison::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(UserAlert::class);
    }

    public function claimedInstitutes(): HasMany
    {
        return $this->hasMany(Institute::class, 'owner_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(InstituteClaim::class);
    }
}
