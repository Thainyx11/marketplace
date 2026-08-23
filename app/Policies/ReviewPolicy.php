<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * A buyer may review a purchased item once it (and only it, since a
     * multi-seller cart ships independently per line) has been delivered.
     */
    public function create(User $user, Order $order, OrderItem $item): bool
    {
        if ($order->buyer_id !== $user->id || $item->order_id !== $order->id) {
            return false;
        }

        return $item->status === 'livree' && ! $item->existingReview();
    }

    /** Deletion is moderation-only, per cahier des charges section 6. */
    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }
}
