<?php

namespace Database\Seeders;

use App\Modules\Invitation\Models\ThemeAsset;
use Illuminate\Database\Seeder;

/**
 * Aset awal Pustaka. File SVG-nya ORISINAL (dibuat dari bentuk dasar,
 * bukan unduhan) — tersedia di paket rilis folder `storage-assets/`.
 * Salin dulu ke storage/app/public/assets-pustaka/ lalu jalankan:
 *   php artisan db:seed --class=Database\\Seeders\\ThemeAssetSeeder
 * Aman diulang (updateOrCreate).
 */
class ThemeAssetSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Floral Dusty Blue (pojok)', 'category' => 'ornament', 'path' => 'assets-pustaka/floral-dusty-blue.svg'],
            ['name' => 'Floral Sage (pojok)',       'category' => 'ornament', 'path' => 'assets-pustaka/floral-sage.svg'],
            ['name' => 'Garis Lengkung Emas',       'category' => 'divider',  'path' => 'assets-pustaka/divider-arc-gold.svg'],
        ] as $asset) {
            ThemeAsset::updateOrCreate(['path' => $asset['path']], $asset + ['is_active' => true]);
        }

        $this->command?->info('Pustaka aset terisi. Pastikan file SVG sudah disalin ke storage/app/public/assets-pustaka/.');
    }
}
