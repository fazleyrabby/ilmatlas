<?php

namespace App\Modules\SEO\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = ['from_path', 'from_path_hash', 'to_path', 'status_code', 'is_active'];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $redirect) {
            $redirect->from_path = '/'.trim($redirect->from_path, '/');
            $redirect->from_path_hash = hash('sha256', $redirect->from_path);
        });
    }
}
