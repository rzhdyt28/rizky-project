<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            // Theme privat milik 1 undangan (child theme) -- null = tema dasar/publik.
            // cascadeOnDelete: child theme mati bareng undangannya.
            $table->foreignId('invitation_id')->nullable()->after('parent_id')
                ->constrained('invitations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invitation_id');
        });
    }
};
