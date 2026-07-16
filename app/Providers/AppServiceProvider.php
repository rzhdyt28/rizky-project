<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // Gate::policy(
        //     \App\Modules\Invitation\Models\Invitation::class,
        //     \App\Modules\Invitation\Policies\InvitationPolicy::class
        // );
    }
}
