<?php

namespace App\Modules\Invitation\Policies;

use App\Modules\Invitation\Models\Invitation;
use App\Core\Models\User;

class InvitationPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return $invitation->tenant?->owner_user_id === $user->id;
    }

    public function update(User $user, Invitation $invitation): bool
    {
        return $this->view($user, $invitation);
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        return $this->view($user, $invitation);
    }
}
