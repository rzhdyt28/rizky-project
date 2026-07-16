<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Registry tema. `component_key` = nama folder komponen Vue di resources/js/themes/*
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('component_key')->unique();      // "elegant", "rustic", tema buatanmu
            $table->string('preview_image')->nullable();
            $table->enum('tier', ['free', 'premium', 'platinum'])->default('free');
            $table->json('default_options')->nullable();    // warna/aksen default tema
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
