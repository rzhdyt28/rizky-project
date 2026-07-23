<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'skripsi';

    public function up(): void
    {
        Schema::connection('skripsi')->create('saw_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('result_snapshot')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('skripsi')->create('saw_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('saw_cases')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight', 8, 4);
            $table->enum('type', ['benefit', 'cost']);
            $table->timestamps();
        });

        Schema::connection('skripsi')->create('saw_alternatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('saw_cases')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::connection('skripsi')->create('saw_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alternative_id')->constrained('saw_alternatives')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('saw_criteria')->cascadeOnDelete();
            $table->decimal('value', 12, 4);
            $table->timestamps();
            $table->unique(['alternative_id', 'criterion_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('skripsi')->dropIfExists('saw_scores');
        Schema::connection('skripsi')->dropIfExists('saw_alternatives');
        Schema::connection('skripsi')->dropIfExists('saw_criteria');
        Schema::connection('skripsi')->dropIfExists('saw_cases');
    }
};
