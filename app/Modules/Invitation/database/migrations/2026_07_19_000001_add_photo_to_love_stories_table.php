<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FOTO PER-KISAH — tampil di section "Kisah Kami" bila toggle foto aktif. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('love_stories', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('story');
        });
    }

    public function down(): void
    {
        Schema::table('love_stories', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};
