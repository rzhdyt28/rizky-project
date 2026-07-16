<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tenant = 1 akun pelanggan (pemilik undangan). stancl/tenancy compatible.
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary(); // uuid/slug tenant
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->json('data')->nullable();
            $table->timestamps();
        });

        // Domain / subdomain milik tenant -> reza-mega.undangan.com atau customdomain.com
        Schema::create('domains', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain')->unique();
            $table->string('tenant_id');
            $table->boolean('is_custom')->default(false);
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
        Schema::dropIfExists('tenants');
    }
};
