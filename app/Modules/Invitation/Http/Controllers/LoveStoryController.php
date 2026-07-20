<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Core\Services\PlanLimitService;
use App\Modules\Invitation\Http\Controllers\Concerns\ManagesInvitationChildren;
use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\LoveStory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Manajemen kisah cinta milik PEMILIK undangan — dipakai dashboard Vue.
 * Field & aturan sama dengan StoriesRelationManager Filament.
 */
class LoveStoryController extends Controller
{
    use ManagesInvitationChildren;

    public function __construct(private PlanLimitService $limits) {}

    public function index(Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        return $invitation->stories()->orderBy('sort_order')->get();
    }

    public function store(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        abort_unless(
            $this->limits->canAddStory($invitation->tenant, $invitation->stories()->count()),
            402, 'Kuota kisah cinta pada paketmu sudah habis. Upgrade paket untuk menambah.'
        );

        $data = $this->validated($request);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('stories', 'public');
        }

        return response()->json($invitation->stories()->create($data), 201);
    }

    public function update(Request $request, Invitation $invitation, LoveStory $story)
    {
        $this->authorize('update', $invitation);
        $this->ensureBelongsToInvitation($story, $invitation);

        $data = $this->validated($request, sometimes: true);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('stories', 'public');
        }
        $story->update($data);

        return $story->fresh();
    }

    public function destroy(Invitation $invitation, LoveStory $story)
    {
        $this->authorize('update', $invitation);
        $this->ensureBelongsToInvitation($story, $invitation);

        $story->delete();

        return response()->noContent();
    }

    private function validated(Request $request, bool $sometimes = false): array
    {
        return $request->validate([
            'title'       => [$this->req($sometimes), 'string', 'max:150'],
            'happened_at' => ['nullable', 'date'],
            'story'       => [$this->req($sometimes), 'string', 'max:2000'],
            'photo'       => ['nullable', 'image', 'max:2048'],
            'sort_order'  => ['nullable', 'integer'],
        ]);
    }
}
