<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{productId}.{userIdA}.{userIdB}', function ($user, $productId, $userIdA, $userIdB) {
    return in_array((int) $user->id, [(int) $userIdA, (int) $userIdB], true);
});
