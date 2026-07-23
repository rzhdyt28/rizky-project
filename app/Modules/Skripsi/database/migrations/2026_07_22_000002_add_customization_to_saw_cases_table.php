<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'skripsi';

    public function up(): void
    {
        Schema::connection('skripsi')->table('saw_cases', function (Blueprint $table) {
            $table->string('alternative_label')->default('Alternatif')->after('title');
            $table->boolean('show_description')->default(true)->after('description');
        });
    }

    public function down(): void
    {
        Schema::connection('skripsi')->table('saw_cases', function (Blueprint $table) {
            $table->dropColumn(['alternative_label', 'show_description']);
        });
    }
};
