<?php

namespace App\Modules\Invitation\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Modules\Invitation\Models\Invitation;
use Illuminate\Http\Request;

class GuestbookController extends Controller
{
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
}
