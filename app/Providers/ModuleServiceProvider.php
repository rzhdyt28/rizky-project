<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * INTI SISTEM MODULAR.
 * Otomatis menemukan setiap folder di app/Modules/* (kecuali _template)
 * lalu me-load:
 *   - routes.php            -> prefix /api/{nama-modul-kebab}
 *   - database/migrations/  -> ikut terbaca `php artisan migrate`
 *
 * Menambah modul baru = copy folder _template, ganti nama, selesai.
 * TIDAK perlu daftar manual di file mana pun.
 */
class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $modulesPath = app_path('Modules');

        foreach (File::directories($modulesPath) as $modulePath) {
            $moduleName = basename($modulePath);
            if (str_starts_with($moduleName, '_')) {
                continue; // _template dilewati
            }

            // 1) Migrations per modul
            if (File::isDirectory("$modulePath/database/migrations")) {
                $this->loadMigrationsFrom("$modulePath/database/migrations");
            }

            // 2) Routes per modul -> /api/portfolio/..., /api/invitation/...
            if (File::exists("$modulePath/routes.php")) {
                Route::prefix('api/'.str($moduleName)->kebab())
                    ->middleware('api')
                    ->group("$modulePath/routes.php");
            }
        }

        // Core routes (auth, billing) -> /api/...
        if (File::exists(app_path('Core/routes.php'))) {
            Route::prefix('api')->middleware('api')->group(app_path('Core/routes.php'));
        }
    }
}
