<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function view(User $user, Message $message): bool
    {
        return in_array($user->id, [$message->sender_id, $message->receiver_id]) || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /** Anyone party to the thread may flag it; moderation happens in the back office. */
    public function report(User $user, Message $message): bool
    {
        return in_array($user->id, [$message->sender_id, $message->receiver_id]);
    }
}
