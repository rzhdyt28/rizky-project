<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DAFTAR TAMU per undangan — sumber link personal (?to=Nama) dan
 * tombol bagikan WhatsApp di dashboard pemilik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 30)->nullable();   // untuk tombol WA (opsional)
            $table->string('note', 160)->nullable();   // mis. "teman kantor"
            $table->timestamps();

            $table->index('invitation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
