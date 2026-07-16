<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Core\Services\PlanLimitService;
use App\Modules\Invitation\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PublicInvitationController extends Controller
{
    /** Halaman publik: /api/invitation/p/{slug}?to=Nama%20Tamu */
    public function show(Request $request, string $slug)
    {
        $invitation = Invitation::withoutGlobalScope('tenant')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['theme', 'events', 'stories', 'photos', 'gifts', 'tenant'])
            ->firstOrFail();

        $features = app(PlanLimitService::class)->featuresFor($invitation->tenant);

        // Merge default_options tema + override per-undangan
        $themeDefaults = $invitation->theme?->default_options ?? [];
        $overrides     = $invitation->theme_options ?? [];
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
}
