<?php

namespace Database\Seeders;

use App\Core\Models\Plan;
use App\Modules\Invitation\Models\Theme;
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

        // ---- Tema bawaan (component_key = folder Vue di rizky-project-web:
        //      src/modules/invitation/themes/<key>/) — HARUS match folder yang
        //      benar-benar ada di FE, kalau tidak tema tidak akan bisa dirender.
        //      Warna/font di sini disalin dari tokens.js masing-masing tema
        //      (satu sumber; kalau kosong pun FE tetap fallback ke tokens.js). ----
        foreach ([
            ['name' => 'Mildness', 'component_key' => 'mildness', 'tier' => 'platinum', 'default_options' => [
                'colors' => [
                    'accent' => '#3F5B7C', 'paper' => '#FBFCFE', 'ink' => '#46586A',
                    'gold' => '#8AA1BC', 'button_bg' => '#3F5B7C', 'button_text' => '#FFFFFF',
                ],
                'fonts' => ['heading' => 'Cormorant Garamond', 'body' => 'Jost', 'script' => 'Great Vibes'],
            ]],
            ['name' => 'Senja', 'component_key' => 'senja', 'tier' => 'platinum', 'default_options' => [
                'colors' => [
                    'accent' => '#8A4B2A', 'paper' => '#FFF8F0', 'ink' => '#5A4234',
                    'gold' => '#D9A05B', 'button_bg' => '#F5E9D7', 'button_text' => '#4A2318',
                ],
                'fonts' => ['heading' => 'Cormorant Garamond', 'body' => 'Jost', 'script' => 'Great Vibes'],
            ]],
        ] as $theme) {
            Theme::updateOrCreate(['component_key' => $theme['component_key']], $theme + ['is_active' => true]);
        }

        // ---- Pustaka aset + undangan demo lengkap (1 per tema aktif) ----
        $this->call([
            ThemeAssetSeeder::class,
            InvitationDemoSeeder::class,
        ]);
    }
}
