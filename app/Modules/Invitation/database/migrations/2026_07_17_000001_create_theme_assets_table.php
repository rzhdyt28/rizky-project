<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PUSTAKA ASET — ornamen/monogram/divider milik ADMIN, global lintas tema
 * dan lintas undangan. SENGAJA terpisah dari gallery_photos karena:
 * - gallery_photos milik undangan user (ter-scope tenant, ikut terhapus
 *   bersama undangan); aset pustaka tidak boleh ikut nasib itu.
 * - query galeri user tidak perlu menyaring aset admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // nama tampil di dropdown
            $table->string('category')->default('ornament'); // ornament|divider|monogram|lainnya
            $table->string('path');                        // path di disk public
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_assets');
    }
};
