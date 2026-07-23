<?php

namespace Database\Seeders;

use App\Modules\Invitation\Models\Theme;
use Illuminate\Database\Seeder;

/**
 * Tema DASAR untuk produk Undangan Online — SENGAJA cuma 2:
 *   component_key HARUS match folder Vue yang benar-benar ada di FE
 *   (rizky-project-web/src/modules/invitation/themes/<key>/), kalau tidak
 *   tema tidak akan bisa dirender sama sekali.
 * Warna/font di sini disalin dari tokens.js masing-masing tema (satu sumber;
 * kalau kosong pun FE tetap fallback ke tokens.js sendiri).
 *
 * Aman dijalankan berkali-kali (updateOrCreate).
 */
class ThemeSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
