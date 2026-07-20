<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Core\Services\PlanLimitService;
use App\Modules\Invitation\Http\Controllers\Concerns\ManagesInvitationChildren;
use App\Modules\Invitation\Models\GalleryPhoto;
use App\Modules\Invitation\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Manajemen galeri foto milik PEMILIK undangan — dipakai dashboard Vue.
 * Field & aturan sama dengan GalleryPhotosRelationManager Filament.
 */
class GalleryPhotoController extends Controller
{
    use ManagesInvitationChildren;

    public function __construct(private PlanLimitService $limits) {}

    public function index(Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        return $invitation->photos()->orderBy('sort_order')->get();
    }

    public function store(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        abort_unless(
            $this->limits->canUploadPhoto($invitation->tenant, $invitation->photos()->count()),
            402, 'Kuota foto galeri pada paketmu sudah habis. Upgrade paket untuk menambah.'
        );

        $data = $request->validate([
            'photo'   => ['required', 'image', 'max:4096'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $photo = $invitation->photos()->create([
            'path'       => $request->file('photo')->store('gallery', 'public'),
            'caption'    => $data['caption'] ?? null,
            'sort_order' => $invitation->photos()->max('sort_order') + 1,
        ]);

        return response()->json($photo, 201);
    }

    public function update(Request $request, Invitation $invitation, GalleryPhoto $photo)
    {
        $this->authorize('update', $invitation);
        $this->ensureBelongsToInvitation($photo, $invitation);

        $data = $request->validate(['caption' => ['nullable', 'string', 'max:255']]);
        $photo->update($data);

        return $photo->fresh();
    }

    public function destroy(Invitation $invitation, GalleryPhoto $photo)
    {
        $this->authorize('update', $invitation);
        $this->ensureBelongsToInvitation($photo, $invitation);

        $photo->delete();

        return response()->noContent();
    }

    /** Urutan drag & drop — body: { order: [id1, id2, ...] }. */
    public function reorder(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $data = $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']]);
        $ids = $invitation->photos()->pluck('id');

        foreach ($data['order'] as $i => $id) {
            if ($ids->contains($id)) {
                GalleryPhoto::whereKey($id)->update(['sort_order' => $i]);
            }
        }

        return $invitation->photos()->orderBy('sort_order')->get();
    }
}
