<?php

namespace App\Modules\Invitation\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Modules\Invitation\Http\Controllers\Concerns\ManagesInvitationChildren;
use App\Modules\Invitation\Models\GuestbookEntry;
use App\Modules\Invitation\Models\Invitation;
use Illuminate\Http\Request;

class GuestbookController extends Controller
{
    use ManagesInvitationChildren;

    public function store(Request $request, string $slug)
    {
        $invitation = Invitation::withoutGlobalScope('tenant')
            ->where('slug', $slug)->where('status', 'published')->firstOrFail();

        abort_unless($invitation->guestbook_enabled, 403, 'Buku ucapan dinonaktifkan.');

        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:120'],
            'message'    => ['required', 'string', 'max:1000'],
        ]);

        $entry = $invitation->guestbook()->create($data + ['is_approved' => true]);

        return response()->json($entry, 201);
    }

    /** Pemilik — semua ucapan (termasuk yang disembunyikan), untuk moderasi. */
    public function index(Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        return $invitation->guestbookEntries()->latest()->get();
    }

    /** Pemilik — sembunyikan/tampilkan ucapan. */
    public function update(Request $request, Invitation $invitation, GuestbookEntry $entry)
    {
        $this->authorize('update', $invitation);
        $this->ensureBelongsToInvitation($entry, $invitation);

        $data = $request->validate(['is_approved' => ['required', 'boolean']]);
        $entry->update($data);

        return $entry->fresh();
    }

    public function destroy(Invitation $invitation, GuestbookEntry $entry)
    {
        $this->authorize('update', $invitation);
        $this->ensureBelongsToInvitation($entry, $invitation);

        $entry->delete();

        return response()->noContent();
    }
}
