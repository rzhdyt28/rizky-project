# Registrasi manual di skeleton Laravel 12 (bootstrap/app.php & providers)

1. Daftarkan ModuleServiceProvider di `bootstrap/providers.php`:
```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ModuleServiceProvider::class,   // <- tambah baris ini
];
```

2. Alias middleware di `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'subscription.active' => App\Core\Http\Middleware\EnsureSubscriptionActive::class,
        'role'                => Spatie\Permission\Middleware\RoleMiddleware::class,
    ]);
    $middleware->statefulApi();   // <- WAJIB untuk Sanctum SPA (Vue terpisah)
})
```

3. Model User: skeleton membuat `app/Models/User.php`. HAPUS file itu dan
   arahkan auth config ke model kita:
   `config/auth.php` -> 'model' => App\Core\Models\User::class

4. Policy: daftarkan di `app/Providers/AppServiceProvider.php` method boot():
```php
Gate::policy(
    \App\Modules\Invitation\Models\Invitation::class,
    \App\Modules\Invitation\Policies\InvitationPolicy::class
);
```

5. Filament: `php artisan filament:install --panels` lalu di
   `app/Providers/Filament/AdminPanelProvider.php`:
   - ->path('admin')
   - ->pages([App\Filament\Pages\Dashboard::class])   // dashboard kustom
   - hapus Pages\Dashboard::class bawaan dari ->pages()
   - ->domain(env('ADMIN_DOMAIN'))  // opsional: admin.rizky.com

6. Tenant model: `config/tenancy.php` -> 'tenant_model' => App\Core\Models\Tenant::class
