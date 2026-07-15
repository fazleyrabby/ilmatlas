<?php

namespace App\Providers;

use App\Modules\Institute\Events\InstituteArchived;
use App\Modules\Institute\Events\InstituteCreated;
use App\Modules\Institute\Events\InstitutePublished;
use App\Modules\Institute\Events\InstituteUpdated;
use App\Modules\Institute\Listeners\ClearInstituteCache;
use App\Modules\Institute\Listeners\QueueInstituteReindex;
use App\Modules\Institute\Models\Institute;
use App\Modules\Institute\Policies\InstitutePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Institute::class => InstitutePolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();

        Event::listen(
            InstituteCreated::class,
            [QueueInstituteReindex::class, 'handle'],
        );

        Event::listen(
            InstituteUpdated::class,
            [QueueInstituteReindex::class, 'handle'],
        );

        Event::listen(
            InstituteUpdated::class,
            [ClearInstituteCache::class, 'handle'],
        );

        Event::listen(
            InstitutePublished::class,
            [QueueInstituteReindex::class, 'handle'],
        );

        Event::listen(
            InstituteArchived::class,
            [ClearInstituteCache::class, 'handle'],
        );

        Blade::directive('nonce', function () {
            return 'nonce="<?php echo app(\'csp-nonce\'); ?>"';
        });
    }
}
