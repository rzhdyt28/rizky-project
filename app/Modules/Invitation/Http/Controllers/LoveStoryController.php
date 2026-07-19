<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Core\Services\PlanLimitService;
use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\LoveStory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Manajemen kisah cinta milik PEMILIK undangan — dipakai dashboard Vue.
 * Field & aturan sama dengan StoriesRelationManager Filament.
 */
class LoveStoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private PlanLimitService $limits) {}

    public function index(Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        return $invitation->stories()->orderBy('sort_order')->get();
    }

    public function store(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $max = $this->limits->planFor($invitation->tenant)?->max_love_stories ?? 0;
        abort_unless($invitation->stories()->count() < $max, 402,
            'Kuota kisah cinta pada paketmu sudah habis. Upgrade paket untuk menambah.');

        $data = $this->validated($request);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('stories', 'public');
        }

        return response()->json($invitation->stories()->create($data), 201);
    }

    public function update(Request $request, Invitation $invitation, LoveStory $story)
    {
        $this->authorize('update', $invitation);
        abort_unless($story->invitation_id === $invitation->id, 404);

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
        abort_unless($story->invitation_id === $invitation->id, 404);

        $story->delete();

        return response()->noContent();
    }

    private function validated(Request $request, bool $sometimes = false): array
    {
        $req = fn (string $rule) => $sometimes ? 'sometimes' : $rule;

        return $request->validate([
            'title'       => [$req('required'), 'string', 'max:150'],
            'happened_at' => ['nullable', 'date'],
            'story'       => [$req('required'), 'string', 'max:2000'],
            'photo'       => ['nullable', 'image', 'max:2048'],
            'sort_order'  => ['nullable', 'integer'],
        ]);
    }
}
