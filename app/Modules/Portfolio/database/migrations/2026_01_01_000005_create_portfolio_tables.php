<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Modul Portofolio — bilingual (kolom JSON {id: "...", en: "..."}) meniru struktur rzhdyt28.github.io
    public function up(): void
    {
        Schema::create('portfolio_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('full_name');
            $table->json('headline');                       // {"id": "...", "en": "..."}
            $table->json('about');
            $table->string('location')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->json('socials')->nullable();            // {email, whatsapp, linkedin, github}
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('portfolio_skills', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('category');                     // ~/it-support, ~/networking, ...
            $table->json('title');
            $table->json('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('portfolio_experiences', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('company');
            $table->json('role');
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('bullets');                        // [{id: "...", en: "..."}, ...]
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('portfolio_educations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->json('degree');
            $table->string('institution');
            $table->string('period')->nullable();
            $table->string('gpa')->nullable();
            $table->enum('kind', ['education', 'certification'])->default('education');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('portfolio_contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('sender_name');
            $table->string('sender_email');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['portfolio_contact_messages','portfolio_educations','portfolio_experiences','portfolio_skills','portfolio_profiles'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
