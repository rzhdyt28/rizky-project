<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Modules\Invitation\Models\Gift;
use App\Modules\Invitation\Models\Invitation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * Manajemen kado digital (rekening/e-wallet/QRIS/alamat) milik PEMILIK
 * undangan — dipakai dashboard Vue. Field & aturan sama dengan
 * GiftsRelationManager Filament.
 */
class GiftController extends Controller
{
    use AuthorizesRequests;

    public function index(Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        return $invitation->gifts()->get();
    }

    public function store(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $data = $this->validated($request);
        if ($request->hasFile('qris_image')) {
            $data['qris_image'] = $request->file('qris_image')->store('qris', 'public');
        }

        return response()->json($invitation->gifts()->create($data), 201);
    }

    public function update(Request $request, Invitation $invitation, Gift $gift)
    {
        $this->authorize('update', $invitation);
        abort_unless($gift->invitation_id === $invitation->id, 404);

        $data = $this->validated($request, sometimes: true);
        if ($request->hasFile('qris_image')) {
            $data['qris_image'] = $request->file('qris_image')->store('qris', 'public');
        }
        $gift->update($data);

        return $gift->fresh();
    }

    public function destroy(Invitation $invitation, Gift $gift)
    {
        $this->authorize('update', $invitation);
        abort_unless($gift->invitation_id === $invitation->id, 404);

        $gift->delete();

        return response()->noContent();
    }

    private function validated(Request $request, bool $sometimes = false): array
    {
        $req = fn (string $rule) => $sometimes ? 'sometimes' : $rule;

        return $request->validate([
            'type'             => [$req('required'), Rule::in(['bank', 'ewallet', 'qris', 'address'])],
            'provider'         => ['nullable', 'string', 'max:80'],
            'account_name'     => ['nullable', 'string', 'max:120'],
            'account_number'   => ['nullable', 'string', 'max:80'],
            'qris_image'       => ['nullable', 'image', 'max:2048'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
