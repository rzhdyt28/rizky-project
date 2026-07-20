<?php

namespace App\Modules\Invitation\Support;

use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\Theme;

/**
 * 1 undangan = 1 "child theme" miliknya sendiri (Theme dengan invitation_id
 * terisi), yang mewarisi tema dasar yang dipilih customer/admin lewat
 * parent_id. Semua pengaturan visual (warna, gaya kartu, layout hero, dst —
 * lihat InvitationLookResource) hidup di child theme ini, bukan lagi di
 * Invitation.theme_options.
 *
 * component_key sengaja diisi nilai yang TIDAK PERNAH match folder Vue
 * manapun (`inv-{id}`) — frontend (themes/registry.js: resolveTheme) sudah
 * otomatis fallback ke component_key parent kalau punya sendiri tidak
 * ditemukan, jadi child theme selalu tampil pakai layout Vue tema dasarnya
 * tanpa perlu folder Vue baru.
 */
class InvitationThemeProvisioner
{
    /** Bikin child theme baru untuk undangan yang baru dibuat. */
    public function provision(Invitation $invitation, Theme $baseTheme): Theme
    {
        return Theme::create([
            'name'            => $invitation->slug . ' (custom)',
            'component_key'   => 'inv-' . $invitation->id,
            'parent_id'       => $baseTheme->id,
            'tier'            => $baseTheme->tier,
            'default_options' => [],
            'is_active'       => true,
            'invitation_id'   => $invitation->id,
        ]);
    }

    /**
     * Ganti tema DASAR undangan yang sudah punya child theme — re-parent,
     * BUKAN replace invitation.theme_id, supaya kustomisasi yang sudah
     * dibuat customer di child theme tidak hilang saat ganti tema dasar.
     */
    public function reparent(Theme $childTheme, Theme $newBaseTheme): Theme
    {
        $childTheme->update([
            'parent_id' => $newBaseTheme->id,
            'tier'      => $newBaseTheme->tier,
        ]);

        return $childTheme->fresh();
    }

    /**
     * Satu pintu dipakai baik oleh InvitationController::update() (API
     * customer) maupun EditInvitation (Filament) saat admin/customer ganti
     * tema dasar undangan yang SUDAH ADA. Mengembalikan theme_id final yang
     * harus disimpan ke kolom invitations.theme_id (child theme, tidak
     * pernah tema dasar langsung).
     */
    public function resolveOnBaseThemeChange(Invitation $invitation, Theme $newBaseTheme): int
    {
        if ($invitation->theme?->invitation_id === $invitation->id) {
            $this->reparent($invitation->theme, $newBaseTheme);

            return $invitation->theme_id;
        }

        return $this->provision($invitation, $newBaseTheme)->id;
    }
}
