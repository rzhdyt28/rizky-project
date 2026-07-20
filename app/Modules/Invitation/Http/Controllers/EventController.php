<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Modules\Invitation\Http\Controllers\Concerns\ManagesInvitationChildren;
use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\InvitationEvent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Manajemen acara (Akad, Resepsi, dst) milik PEMILIK undangan — dipakai
 * dashboard Vue. Field & aturan sama dengan EventsRelationManager Filament.
 */
class EventController extends Controller
{
    use ManagesInvitationChildren;

    public function index(Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        return $invitation->events()->orderBy('sort_order')->get();
    }

    public function store(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $data = $this->validated($request);

        return response()->json($invitation->events()->create($data), 201);
    }

    public function update(Request $request, Invitation $invitation, InvitationEvent $event)
    {
        $this->authorize('update', $invitation);
        $this->ensureBelongsToInvitation($event, $invitation);

        $event->update($this->validated($request, sometimes: true));

        return $event->fresh();
    }

    public function destroy(Invitation $invitation, InvitationEvent $event)
    {
        $this->authorize('update', $invitation);
        $this->ensureBelongsToInvitation($event, $invitation);

        $event->delete();

        return response()->noContent();
    }

    private function validated(Request $request, bool $sometimes = false): array
    {
        return $request->validate([
            'title'      => [$this->req($sometimes), 'string', 'max:150'],
            'starts_at'  => [$this->req($sometimes), 'date'],
            'ends_at'    => ['nullable', 'date', 'after_or_equal:starts_at'],
            'venue_name' => [$this->req($sometimes), 'string', 'max:150'],
            'address'    => ['nullable', 'string', 'max:500'],
            'maps_url'   => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }
}
