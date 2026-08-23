<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->id === $order->buyer_id) {
            return true;
        }

        return $order->items()->where('seller_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAcheteur();
    }

    /**
     * Only an admin may override an order's overall status directly
     * (dispute resolution) — sellers advance their own line items instead,
     * see OrderItem::status and Order::recomputeStatus().
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
