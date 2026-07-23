<?php

namespace Database\Seeders;

use App\Core\Models\Plan;
use App\Core\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Role & permission (spatie/laravel-permission) ----
        $permissions = [
            'manage-users', 'manage-plans', 'manage-themes', 'manage-coupons',
            'view-logs', 'manage-invitations', 'manage-portfolio',
        ];
        foreach ($permissions as $p) {
            Permission::findOrCreate($p);
        }

        Role::findOrCreate('super-admin')->givePermissionTo(Permission::all());
        Role::findOrCreate('admin')->givePermissionTo(['manage-themes', 'manage-coupons', 'view-logs']);
        Role::findOrCreate('user'); // pelanggan pembuat undangan

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Super Admin', 'password' => 'password']
        );
        $admin->assignRole('super-admin');

        // ---- Paket komersial ----
        foreach ([
            ['name' => 'Free',     'slug' => 'free',     'price' => 0,      'max_invitations' => 1, 'max_guests' => 50,   'max_photos' => 4,  'custom_domain' => false, 'remove_branding' => false, 'music_enabled' => false],
            ['name' => 'Premium',  'slug' => 'premium',  'price' => 149000, 'max_invitations' => 1, 'max_guests' => 300,  'max_photos' => 20, 'custom_domain' => false, 'remove_branding' => true,  'music_enabled' => true],
            ['name' => 'Platinum', 'slug' => 'platinum', 'price' => 299000, 'max_invitations' => 3, 'max_guests' => 1000, 'max_photos' => 60, 'custom_domain' => true,  'remove_branding' => true,  'music_enabled' => true],
        ] as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        // ---- Pustaka aset + undangan demo lengkap (1 per tema aktif) ----
        $this->call([
            ThemeSeeder::class,
            PlanFeatureSeeder::class,
            ThemeAssetSeeder::class,
            InvitationDemoSeeder::class,
        ]);
    }
}
