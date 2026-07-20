<?php

namespace App\Modules\Invitation\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\Theme;
use App\Modules\Invitation\Support\InvitationThemeProvisioner;
use App\Core\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private PlanLimitService $limits,
        private InvitationThemeProvisioner $themeProvisioner,
    ) {}

    /** Daftar tema DASAR (bukan child theme privat) yang boleh dipakai tenant — untuk dropdown Edit Undangan. */
    public function themes(Request $request)
    {
        $tenant = $request->user()->tenants()->first();

        return Theme::query()->whereNull('invitation_id')->get(['id', 'name', 'tier'])
            ->filter(fn ($t) => $this->limits->canUseTheme($tenant, $t->tier))
            ->values();
    }

    public function index(Request $request)
    {
        // BelongsToTenant global scope HANYA aktif saat tenancy()->initialized
        // (rute subdomain, routes/tenant.php) — rute dashboard ini diakses lewat
        // domain sentral, jadi tenancy TIDAK PERNAH initialized di sini. Scope
        // eksplisit ke tenant milik user yang login, sama seperti pola yang
        // dipakai EnsureSubscriptionActive middleware.
        $tenantIds = $request->user()->tenants()->pluck('id');

        return Invitation::withoutGlobalScope('tenant')
            ->whereIn('tenant_id', $tenantIds)
            ->with('theme')->latest()->paginate(15);
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

        $baseTheme = Theme::whereNull('invitation_id')->findOrFail($data['theme_id']);
        abort_unless($this->limits->canUseTheme($tenant, $baseTheme->tier), 402,
            "Tema {$baseTheme->name} hanya tersedia di paket {$baseTheme->tier}.");

        // FIX BUG lama: dulu default_options tema DISALIN (snapshot) ke undangan,
        // membuat semua perubahan Tema di Filament tidak pernah berefek. Sekarang
        // undangan dapat CHILD THEME sendiri (invitation_id terisi, parent_id =
        // tema dasar) — semua pengaturan visual hidup di sana, digabung live
        // lewat ancestryChain() saat render (PublicInvitationController).
        $invitation = Invitation::create(collect($data)->except('theme_id')->all());
        $childTheme = $this->themeProvisioner->provision($invitation, $baseTheme);
        $invitation->update(['theme_id' => $childTheme->id]);

        activity()->performedOn($invitation)->log('invitation.created');

        return response()->json($invitation->fresh('theme'), 201);
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
            'slug'              => ['sometimes', 'alpha_dash', 'max:80', Rule::unique('invitations', 'slug')->ignore($invitation->id)],
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
            'video_url'         => ['nullable', 'url', 'max:255'],
            'music_url'         => ['nullable', 'string', 'max:255'],
            'co_hosts'          => ['nullable', 'array'],
            'co_hosts.*.name'   => ['required_with:co_hosts', 'string', 'max:150'],
            'co_hosts.*.side'   => ['nullable', Rule::in(['pria', 'wanita', 'spesial'])],
        ]);

        if (($data['status'] ?? null) === 'published' && ! $invitation->published_at) {
            $data['published_at'] = now();
        }

        // Ganti tema DASAR = plug & play: data & kustomisasi visual tidak hilang,
        // cuma "skin". Re-parent child theme yang sudah ada (bukan replace
        // invitation.theme_id) — child theme TETAP sama, cuma parent_id-nya ganti.
        if (isset($data['theme_id'])) {
            $newBaseTheme = Theme::whereNull('invitation_id')->findOrFail($data['theme_id']);
            abort_unless($this->limits->canUseTheme(tenant(), $newBaseTheme->tier), 402,
                "Tema {$newBaseTheme->name} butuh paket {$newBaseTheme->tier}.");

            $data['theme_id'] = $this->themeProvisioner->resolveOnBaseThemeChange($invitation, $newBaseTheme);
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

    /**
     * Upload generik untuk field theme_options berbasis file (foto hero,
     * background, ornamen, musik) yang di Filament dipakai lewat FileUpload
     * bawaan — SPA butuh endpoint sendiri karena tidak lewat Livewire.
     * Mengembalikan path tersimpan; caller menyimpannya sendiri ke
     * theme_options yang sesuai lewat update() seperti biasa.
     */
    public function upload(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $data = $request->validate([
            'file'      => ['required', 'file', 'max:15360'],
            'directory' => ['required', Rule::in(['covers', 'couple', 'section-bg', 'ornaments', 'music'])],
        ]);

        $isMusic = $data['directory'] === 'music';
        $request->validate([
            'file' => $isMusic ? ['mimes:mp3,mpeg'] : ['image'],
        ]);

        $path = $request->file('file')->store($data['directory'], 'public');

        return response()->json(['path' => $path]);
    }
}
