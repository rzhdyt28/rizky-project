<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PEWARISAN TEMA — tema anak mewarisi default_options parent-nya.
 * nullOnDelete: menghapus parent TIDAK menghapus anak; anak hanya
 * kehilangan warisannya (berdiri sendiri lagi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('component_key')
                ->constrained('themes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
