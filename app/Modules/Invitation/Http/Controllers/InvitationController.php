<?php

namespace App\Modules\Invitation\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\Theme;
use App\Core\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    use AuthorizesRequests;
    public function __construct(private PlanLimitService $limits) {}

    public function index(Request $request)
    {
        // Global scope BelongsToTenant sudah memfilter per tenant
        return Invitation::with('theme')->latest()->paginate(15);
    }

    public function store(Request $request)
    {
        $tenant = tenant();
        abort_unless($this->limits->canCreateInvitation($tenant), 402,
            'Kuota undangan pada paketmu sudah habis. Upgrade paket untuk menambah.');

        $data = $request->validate([
            'slug'         => ['required', 'alpha_dash', 'max:80', Rule::unique('invitations', 'slug')],
            'groom_name'   => ['required', 'string', 'max:120'],
            'bride_name'   => ['required', 'string', 'max:120'],
            'theme_id'     => ['required', 'exists:themes,id'],
            'opening_text' => ['nullable', 'string'],
        ]);

        $theme = Theme::findOrFail($data['theme_id']);
        abort_unless($this->limits->canUseTheme($tenant, $theme->tier), 402,
            "Tema {$theme->name} hanya tersedia di paket {$theme->tier}.");

        $invitation = Invitation::create($data + ['theme_options' => $theme->default_options]);

        activity()->performedOn($invitation)->log('invitation.created');

        return response()->json($invitation, 201);
    }

    public function show(Invitation $invitation)
    {
        $this->authorize('view', $invitation);
        return $invitation->load(['theme', 'events', 'stories', 'photos', 'gifts']);
    }

    public function update(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $data = $request->validate([
            'groom_name'        => ['sometimes', 'string', 'max:120'],
            'bride_name'        => ['sometimes', 'string', 'max:120'],
            'groom_parents'     => ['nullable', 'string', 'max:255'],
            'bride_parents'     => ['nullable', 'string', 'max:255'],
            'opening_text'      => ['nullable', 'string'],
            'theme_id'          => ['sometimes', 'exists:themes,id'],
            'theme_options'     => ['nullable', 'array'],
            'rsvp_enabled'      => ['sometimes', 'boolean'],
            'guestbook_enabled' => ['sometimes', 'boolean'],
            'status'            => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
        ]);

        if (($data['status'] ?? null) === 'published' && ! $invitation->published_at) {
            $data['published_at'] = now();
        }

        // Ganti tema = plug & play: data tidak berubah, hanya skin
        if (isset($data['theme_id'])) {
            $theme = Theme::findOrFail($data['theme_id']);
            abort_unless($this->limits->canUseTheme(tenant(), $theme->tier), 402,
                "Tema {$theme->name} butuh paket {$theme->tier}.");
        }

        $invitation->update($data);

        return $invitation->fresh('theme');
    }

    public function destroy(Invitation $invitation)
    {
        $this->authorize('delete', $invitation);
        $invitation->delete();

        return response()->noContent();
    }
}
