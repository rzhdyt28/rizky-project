<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('music_url');   // link YouTube/embed
            $table->json('co_hosts')->nullable()->after('video_url');      // ["Kel. Bpk Ahmad", ...]
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'co_hosts']);
        });
    }
};
