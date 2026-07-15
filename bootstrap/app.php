<?php

use App\Http\Middleware\AdminAccess;
use App\Http\Middleware\NotAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Modules\SEO\Http\Middleware\RedirectMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            require base_path('routes/web.php');
            foreach (glob(base_path('app/Modules/*/Routes/*.php')) as $file) {
                require $file;
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminAccess::class,
            'not_admin' => NotAdmin::class,
        ]);

        $middleware->prependToGroup('web', RedirectMiddleware::class);
        $middleware->appendToGroup('web', SecurityHeaders::class);

        $middleware->redirectGuestsTo('/admin/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
