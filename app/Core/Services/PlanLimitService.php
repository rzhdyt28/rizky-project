<?php

namespace App\Core\Services;

use App\Core\Models\Plan;
use App\Core\Models\Tenant;

/**
 * Pembatasan fitur per paket komersial (tiering).
 * featuresFor() = sumber tunggal daftar fitur yang boleh, dikonsumsi API -> Vue.
 */
class PlanLimitService
{
    public function planFor(Tenant $tenant): ?Plan
    {
        return $tenant->activeSubscription?->plan;
    }

    public function canCreateInvitation(Tenant $tenant): bool
    {
        $plan = $this->planFor($tenant);

        return $plan && $tenant->invitations()->count() < $plan->max_invitations;
    }

    public function canUploadPhoto(Tenant $tenant, int $invitationPhotoCount): bool
    {
        $plan = $this->planFor($tenant);

        return $plan && $invitationPhotoCount < $plan->max_photos;
    }

    public function canAddStory(Tenant $tenant, int $invitationStoryCount): bool
    {
        $plan = $this->planFor($tenant);

        return $plan && $invitationStoryCount < $plan->max_love_stories;
    }

    public function canAddGuest(Tenant $tenant, int $invitationGuestCount): bool
    {
        $plan = $this->planFor($tenant);

        return $plan && $invitationGuestCount < $plan->max_guests;
    }

    public function canUseCustomDomain(Tenant $tenant): bool
    {
        return (bool) $this->planFor($tenant)?->custom_domain;
    }

    public function canUseTheme(Tenant $tenant, string $themeTier): bool
    {
        $order = ['free' => 0, 'premium' => 1, 'platinum' => 2];
        $slug  = $this->planFor($tenant)?->slug ?? 'free';

        return ($order[$slug] ?? 0) >= ($order[$themeTier] ?? 0);
    }

    /** Daftar lengkap fitur berdasarkan paket aktif tenant (untuk show/hide di Vue). */
    public function featuresFor(?Tenant $tenant): array
    {
        $plan = $tenant ? $this->planFor($tenant) : null;

        if (! $plan) {
            return $this->defaults();
        }

        return [
            'music'             => (bool) $plan->music_enabled,
            'gallery'           => (bool) $plan->gallery_enabled,
            'love_story'        => (bool) $plan->love_story_enabled,
            'gift'              => (bool) $plan->gift_enabled,
            'countdown'         => (bool) $plan->countdown_enabled,
            'video'             => (bool) $plan->video_enabled,
            'co_host'           => (bool) $plan->co_host_enabled,
            'maps'              => (bool) $plan->maps_enabled,
            'custom_font'       => (bool) $plan->custom_font_enabled,
            'custom_background' => (bool) $plan->custom_background_enabled,
            'custom_ornament'   => (bool) $plan->custom_ornament_enabled,
            'custom_domain'     => (bool) $plan->custom_domain,
            'remove_branding'   => (bool) $plan->remove_branding,
            'max_photos'        => (int) $plan->max_photos,
            'max_love_stories'  => (int) $plan->max_love_stories,
            'max_guests'        => (int) $plan->max_guests,
            'plan_name'         => $plan->name,
        ];
    }

    private function defaults(): array
    {
        return [
            'music' => false, 'gallery' => true, 'love_story' => false, 'gift' => false,
            'countdown' => false, 'video' => false, 'co_host' => false, 'maps' => false,
            'custom_font' => false, 'custom_background' => false, 'custom_ornament' => false,
            'custom_domain' => false, 'remove_branding' => false,
            'max_photos' => 4, 'max_love_stories' => 0, 'max_guests' => 50,
            'plan_name' => 'Free (default)',
        ];
    }
}
