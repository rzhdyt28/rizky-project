<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Core routes (auth, checkout, dll) -> prefix /api
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('app/Core/routes.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->alias([
            'subscription.active' => App\Core\Http\Middleware\EnsureSubscriptionActive::class,
            'role'                => Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);
        $middleware->statefulApi();   // <- WAJIB
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
