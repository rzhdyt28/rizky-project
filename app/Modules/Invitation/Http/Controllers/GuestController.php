<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Modules\Invitation\Models\Guest;
use App\Modules\Invitation\Models\Invitation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Manajemen tamu milik PEMILIK undangan (dipakai dashboard Vue).
 * Link personal & pesan WA dirakit di frontend dari data ini.
 */
class GuestController extends Controller
{
    use AuthorizesRequests;

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
        abort_unless($guest->invitation_id === $invitation->id, 404);

        $guest->delete();

        return response()->noContent();
    }
}
