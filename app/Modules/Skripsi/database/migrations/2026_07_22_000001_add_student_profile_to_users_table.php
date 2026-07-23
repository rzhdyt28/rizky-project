<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'skripsi';

    public function up(): void
    {
        Schema::connection('skripsi')->table('users', function (Blueprint $table) {
            $table->string('nim')->nullable()->after('name');
            $table->string('universitas')->nullable()->after('nim');
            $table->string('jurusan')->nullable()->after('universitas');
            $table->string('angkatan')->nullable()->after('jurusan');
            $table->string('dosen_pembimbing')->nullable()->after('angkatan');
        });
    }

    public function down(): void
    {
        Schema::connection('skripsi')->table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'universitas', 'jurusan', 'angkatan', 'dosen_pembimbing']);
        });
    }
};
