<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Core\Services\PlanLimitService;
use App\Modules\Invitation\Http\Controllers\Concerns\ManagesInvitationChildren;
use App\Modules\Invitation\Models\Guest;
use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Support\GuestSheetImporter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;

/**
 * Manajemen tamu milik PEMILIK undangan (dipakai dashboard Vue).
 * Link personal & pesan WA dirakit di frontend dari data ini.
 */
class GuestController extends Controller
{
    use ManagesInvitationChildren;

    public function __construct(private PlanLimitService $limits) {}

    public function index(Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        return $invitation->guests()->latest()->get();
    }

    public function store(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'note'  => ['nullable', 'string', 'max:160'],
        ]);

        return response()->json($invitation->guests()->create($data), 201);
    }

    public function destroy(Invitation $invitation, Guest $guest)
    {
        $this->authorize('update', $invitation);
        $this->ensureBelongsToInvitation($guest, $invitation);

        $guest->delete();

        return response()->noContent();
    }

    /**
     * Import tamu dari Excel (.xlsx) / CSV — parsing didelegasikan ke
     * GuestSheetImporter (dipakai juga oleh GuestsRelationManager Filament)
     * supaya deteksi delimiter/header konsisten di kedua tempat. Berhenti
     * menambah baris begitu kuota max_guests paket tercapai.
     */
    public function import(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120']]);

        try {
            $rows = GuestSheetImporter::parse($request->file('file')->getRealPath());
        } catch (RuntimeException $e) {
            abort(422, $e->getMessage());
        }

        $existing = $invitation->guests()->count();
        $created = 0;
        $skipped = [];

        foreach ($rows as $i => $row) {
            if (! $this->limits->canAddGuest($invitation->tenant, $existing + $created)) {
                $skipped[] = 'Baris ' . ($i + 1) . ': kuota tamu paket sudah penuh';
                continue;
            }

            $invitation->guests()->create($row);
            $created++;
        }

        return response()->json([
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }
}
