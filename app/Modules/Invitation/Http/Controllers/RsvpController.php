<?php

namespace App\Modules\Invitation\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Modules\Invitation\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RsvpController extends Controller
{
    use AuthorizesRequests;

    /** Publik — tamu submit kehadiran. Dilindungi throttle di routes. */
    public function store(Request $request, string $slug)
    {
        $invitation = Invitation::withoutGlobalScope('tenant')
            ->where('slug', $slug)->where('status', 'published')->firstOrFail();

        abort_unless($invitation->rsvp_enabled, 403, 'RSVP dinonaktifkan.');

        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:120'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'attendance' => ['required', Rule::in(['attending', 'not_attending', 'maybe'])],
            'pax'        => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        return response()->json(
            $invitation->rsvps()->create($data + ['ip_address' => $request->ip()]),
            201
        );
    }

    /** Pemilik — rekap RSVP untuk dashboard. */
    public function index(Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        return [
            'summary' => $invitation->rsvps()
                ->selectRaw('attendance, count(*) as total, sum(pax) as pax')
                ->groupBy('attendance')->get(),
            'items'   => $invitation->rsvps()->latest()->paginate(25),
        ];
    }
}
