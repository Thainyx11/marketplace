<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WantedItem;

class WantedItemPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, WantedItem $wantedItem): bool
    {
        return $user->id === $wantedItem->user_id;
    }

    /** Only approved vendors (or admin) may respond, and only while the request is still open. */
    public function respond(User $user, WantedItem $wantedItem): bool
    {
        return (($user->isVendeur() && $user->is_approved) || $user->isAdmin())
            && $wantedItem->status === 'ouverte';
    }
}
