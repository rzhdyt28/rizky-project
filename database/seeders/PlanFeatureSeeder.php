<?php

namespace Database\Seeders;

use App\Core\Models\Plan;
use Illuminate\Database\Seeder;

/** Jalankan SETELAH migration toggle: php artisan db:seed --class=PlanFeatureSeeder */
class PlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $matrix = [
            'free' => [
                // 'gallery_enabled' => true,  'love_story_enabled' => false, 'gift_enabled' => false,
                // 'countdown_enabled' => false, 'video_enabled' => false, 'co_host_enabled' => false,
                // 'maps_enabled' => false, 'custom_font_enabled' => false,
                // 'custom_background_enabled' => false, 'custom_ornament_enabled' => false,
                // 'music_enabled' => false, 'remove_branding' => false, 'custom_domain' => false,
                // 'max_love_stories' => 0,
                'gallery_enabled' => true, 'love_story_enabled' => true, 'gift_enabled' => true,
                'countdown_enabled' => true, 'video_enabled' => true, 'co_host_enabled' => true,
                'maps_enabled' => true, 'custom_font_enabled' => true,
                'custom_background_enabled' => true, 'custom_ornament_enabled' => true,
                'music_enabled' => true, 'remove_branding' => true, 'custom_domain' => true,
                'max_love_stories' => 99,
            ],
            'premium' => [
                'gallery_enabled' => true, 'love_story_enabled' => true, 'gift_enabled' => true,
                'countdown_enabled' => true, 'video_enabled' => true, 'co_host_enabled' => true,
                'maps_enabled' => true, 'custom_font_enabled' => false,
                'custom_background_enabled' => false, 'custom_ornament_enabled' => false,
                'music_enabled' => true, 'remove_branding' => true, 'custom_domain' => false,
                'max_love_stories' => 5,
            ],
            'platinum' => [
                'gallery_enabled' => true, 'love_story_enabled' => true, 'gift_enabled' => true,
                'countdown_enabled' => true, 'video_enabled' => true, 'co_host_enabled' => true,
                'maps_enabled' => true, 'custom_font_enabled' => true,
                'custom_background_enabled' => true, 'custom_ornament_enabled' => true,
                'music_enabled' => true, 'remove_branding' => true, 'custom_domain' => true,
                'max_love_stories' => 99,
            ],
        ];

        foreach ($matrix as $slug => $features) {
            Plan::where('slug', $slug)->update($features);
        }

        $this->command->info('Toggle fitur per paket ter-update (free/premium/platinum).');
    }
}
