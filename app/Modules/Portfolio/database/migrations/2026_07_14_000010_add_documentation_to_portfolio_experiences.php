<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan field yang dibutuhkan untuk mereplikasi tampilan
     * portfolio bergaya terminal (index.html + portofolio.html):
     * - slug         -> dipakai untuk anchor (#berca, #teguh-karya, dst.)
     * - tags         -> daftar kata kunci di bawah tiap experience (json array of string)
     *
     * Tabel baru portfolio_experience_photos menampung galeri foto
     * dokumentasi per pengalaman kerja (dulunya hardcode di portofolio.html).
     */
    public function up(): void
    {
        Schema::table('portfolio_experiences', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('company');
            $table->json('tags')->nullable()->after('bullets');
        });

        Schema::create('portfolio_experience_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')
                ->constrained('portfolio_experiences')
                ->cascadeOnDelete();
            $table->string('path');
            // caption bilingual, mis. {"id": "Deployment access point", "en": "Access point deployment"}
            $table->json('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_experience_photos');

        Schema::table('portfolio_experiences', function (Blueprint $table) {
            $table->dropColumn(['slug', 'tags']);
        });
    }
};