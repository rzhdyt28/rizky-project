<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Core\Services\PlanLimitService;
use App\Modules\Invitation\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PublicInvitationController extends Controller
{
    /**
     * Halaman publik: /api/invitation/p/{slug}?to=Nama%20Tamu
     * ?preview=1 : pemilik undangan / admin yang sedang login boleh melihat
     * DRAFT sebelum publish — menutup alur "save → tebak-tebakan hasil".
     */
    public function show(Request $request, string $slug)
    {
        $invitation = Invitation::withoutGlobalScope('tenant')
            ->where('slug', $slug)
            ->with(['theme', 'events', 'stories', 'photos', 'gifts', 'tenant'])
            ->firstOrFail();

        if ($invitation->status !== 'published') {
            abort_unless(
                $request->boolean('preview') && $this->canPreview($request, $invitation),
                404
            );
        }

        $features = app(PlanLimitService::class)->featuresFor($invitation->tenant);

        // Merge default_options tema + override per-undangan
        $themeDefaults = $invitation->theme?->default_options ?? [];
        // prune(): field yang DIKOSONGKAN admin di undangan (null/'') dibuang
        // sebelum merge, sehingga benar-benar jatuh ke default Tema.
        // Tanpa ini, snapshot lama berisi null tetap menimpa nilai tema.
        $overrides     = $this->prune($invitation->theme_options ?? []);
        $invitation->setAttribute('theme_options', array_replace_recursive($themeDefaults, $overrides));

        // Jangan ekspos data tenant/pemilik ke publik
        $invitation->makeHidden('tenant');
        $invitation->unsetRelation('tenant');

        return response()->json([
            'invitation' => $invitation,
            'guest_name' => $request->query('to'),
            'guestbook'  => $invitation->guestbook()->limit(50)->get(),
            'features'   => $features,
        ]);
    }

    /** Boleh pratinjau draft: admin, atau pemilik tenant undangan ini. */
    private function canPreview(Request $request, Invitation $invitation): bool
    {
        $user = $request->user('sanctum');
        if (! $user) {
            return false;
        }
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        return $invitation->tenant?->owner_user_id === $user->id;
    }

    /** Hapus nilai null/'' secara rekursif; false dan 0 TETAP dipertahankan
     *  (penting untuk toggle seperti layout.card = false). */
    private function prune(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $v = $this->prune($v);
                if ($v !== []) {
                    $out[$k] = $v;
                }
            } elseif ($v !== null && $v !== '') {
                $out[$k] = $v;
            }
        }

        return $out;
    }
}
