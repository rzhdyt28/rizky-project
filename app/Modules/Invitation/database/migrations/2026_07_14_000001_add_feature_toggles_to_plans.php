<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('gallery_enabled')->default(true)->after('music_enabled');
            $table->boolean('love_story_enabled')->default(false)->after('gallery_enabled');
            $table->boolean('gift_enabled')->default(false)->after('love_story_enabled');
            $table->boolean('countdown_enabled')->default(false)->after('gift_enabled');
            $table->boolean('video_enabled')->default(false)->after('countdown_enabled');
            $table->boolean('co_host_enabled')->default(false)->after('video_enabled');
            $table->boolean('maps_enabled')->default(false)->after('co_host_enabled');
            $table->boolean('custom_font_enabled')->default(false)->after('maps_enabled');
            $table->boolean('custom_background_enabled')->default(false)->after('custom_font_enabled');
            $table->boolean('custom_ornament_enabled')->default(false)->after('custom_background_enabled');
            $table->unsignedInteger('max_love_stories')->default(0)->after('custom_ornament_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'gallery_enabled', 'love_story_enabled', 'gift_enabled', 'countdown_enabled',
                'video_enabled', 'co_host_enabled', 'maps_enabled', 'custom_font_enabled',
                'custom_background_enabled', 'custom_ornament_enabled', 'max_love_stories',
            ]);
        });
    }
};
