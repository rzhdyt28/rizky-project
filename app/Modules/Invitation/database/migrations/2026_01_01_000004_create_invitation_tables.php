<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();               // undangan.com/reza-mega
            $table->string('groom_name');
            $table->string('bride_name');
            $table->string('groom_parents')->nullable();
            $table->string('bride_parents')->nullable();
            $table->text('opening_text')->nullable();
            $table->string('music_url')->nullable();
            $table->json('theme_options')->nullable();      // override warna/aksen per undangan
            $table->boolean('rsvp_enabled')->default(true);
            $table->boolean('guestbook_enabled')->default(true);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('invitation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('title');                        // Akad, Resepsi
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('venue_name');
            $table->text('address')->nullable();
            $table->string('maps_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('love_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('happened_at')->nullable();
            $table->text('story');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['bank', 'ewallet', 'qris', 'address']);
            $table->string('provider')->nullable();         // BCA, OVO, dst.
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('qris_image')->nullable();
            $table->text('shipping_address')->nullable();
            $table->timestamps();
        });

        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('phone')->nullable();
            $table->enum('attendance', ['attending', 'not_attending', 'maybe']);
            $table->unsignedTinyInteger('pax')->default(1);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['invitation_id', 'attendance']);
        });

        Schema::create('guestbook_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');
            $table->text('message');
            $table->boolean('is_approved')->default(true);  // moderasi opsional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['guestbook_entries','rsvps','gifts','gallery_photos','love_stories','invitation_events','invitations'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
