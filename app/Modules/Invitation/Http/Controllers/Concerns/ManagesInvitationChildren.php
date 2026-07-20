<?php

namespace App\Modules\Invitation\Http\Controllers\Concerns;

use App\Modules\Invitation\Models\Invitation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Boilerplate bersama controller anak-Invitation (Event/Gift/LoveStory/
 * GalleryPhoto/Guest/Guestbook): authorize request bawaan Laravel, guard
 * kepemilikan child terhadap invitation, dan helper rule "sometimes" pada
 * update parsial.
 */
trait ManagesInvitationChildren
{
    use AuthorizesRequests;

    /** 404 kalau $child bukan milik $invitation ini (cegah akses lintas-undangan). */
    protected function ensureBelongsToInvitation(Model $child, Invitation $invitation): void
    {
        abort_unless($child->invitation_id === $invitation->id, 404);
    }

    /** 'sometimes' saat update parsial, atau $rule asli (default 'required') saat create. */
    protected function req(bool $sometimes, string $rule = 'required'): string
    {
        return $sometimes ? 'sometimes' : $rule;
    }
}
