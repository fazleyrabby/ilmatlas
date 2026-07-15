<?php

namespace App\Modules\Institute\Listeners;

use App\Modules\Institute\Events\InstituteArchived;
use App\Modules\Institute\Events\InstituteUpdated;
use Illuminate\Support\Facades\Cache;

class ClearInstituteCache
{
    public function handle(InstituteUpdated|InstituteArchived $event): void
    {
        $uuid = $event->institute->uuid;

        Cache::forget("institute:{$uuid}");
        Cache::forget("institute:{$event->institute->id}");
        Cache::forget("institute:{$uuid}:profile");
    }
}
