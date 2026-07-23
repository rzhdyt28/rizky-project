<?php

namespace Database\Seeders;

use App\Modules\Invitation\Models\ThemeAsset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Aset awal Pustaka (ornamen sudut + divider). File SVG-nya ORISINAL (dibuat
 * dari bentuk dasar, bukan unduhan) dan disimpan di GIT (database/seeders/
 * assets/ornaments/) -- BUKAN langsung di storage/app/public/, yang selalu
 * di-gitignore dan gampang hilang kalau folder publik dibersihkan (pernah
 * kejadian). Seeder ini SELALU menyalin ulang dari sumber git ke
 * storage/app/public/undangan/assets-pustaka/ tiap dijalankan, jadi
 * "self-healing" -- tidak perlu lagi langkah salin manual.
 * Aman diulang (File::copy overwrite + ThemeAsset::updateOrCreate).
 */
class ThemeAssetSeeder extends Seeder
{
    /** path relatif (dari assets-pustaka/) => [nama tampil, kategori]. */
    private const ASSETS = [
        'floral-java-gold.svg'     => ['Floral Jawa — Emas', 'ornament'],
        'floral-java-emerald.svg'  => ['Floral Jawa — Emerald', 'ornament'],
        'floral-modern-line.svg'   => ['Floral Modern — Line Art', 'ornament'],
        'floral-modern-blush.svg'  => ['Floral Modern — Blush', 'ornament'],
        'floral-dusty-blue.svg'    => ['Floral Klasik — Dusty Blue', 'ornament'],
        'floral-sage.svg'          => ['Floral Klasik — Sage', 'ornament'],
        'divider-arc-gold.svg'     => ['Garis Lengkung Emas', 'divider'],
        'divider-modern-line.svg'  => ['Garis Modern Minimalis', 'divider'],
        'divider-java.svg'         => ['Garis Motif Jawa', 'divider'],
    ];

    public function run(): void
    {
        $source = database_path('seeders/assets/ornaments');
        $destDir = 'undangan/assets-pustaka';
        Storage::disk('public')->makeDirectory($destDir);

        foreach (self::ASSETS as $file => [$name, $category]) {
            $from = "$source/$file";
            if (! File::exists($from)) {
                $this->command?->warn("Sumber tidak ditemukan: $from -- lewati.");

                continue;
            }

            $relativePath = "$destDir/$file";
            Storage::disk('public')->put($relativePath, File::get($from));

            ThemeAsset::updateOrCreate(
                ['path' => $relativePath],
                ['name' => $name, 'category' => $category, 'is_active' => true]
            );
        }

        $this->command?->info('Pustaka aset ter-seed (' . count(self::ASSETS) . ' file, disalin ulang dari database/seeders/assets/ornaments/).');
    }
}
