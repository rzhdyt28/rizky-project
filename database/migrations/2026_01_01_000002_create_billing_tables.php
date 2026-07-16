<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Paket berlangganan: Free / Premium / Platinum
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('price');            // rupiah
            $table->unsignedInteger('duration_days')->default(365);
            $table->unsignedInteger('max_invitations')->default(1);
            $table->unsignedInteger('max_guests')->default(100);
            $table->unsignedInteger('max_photos')->default(10);
            $table->boolean('custom_domain')->default(false);
            $table->boolean('remove_branding')->default(false);
            $table->boolean('music_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percent', 'fixed']);
            $table->unsignedBigInteger('value');
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('plan_id')->constrained();
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();           // dikirim ke Midtrans
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('gross_amount');
            $table->string('payment_type')->nullable();
            $table->enum('status', ['pending', 'settlement', 'expire', 'cancel', 'deny', 'refund'])->default('pending');
            $table->string('snap_token')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('plans');
    }
};
