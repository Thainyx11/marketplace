<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Product $product): bool
    {
        if ($product->status === 'active') {
            return true;
        }

        return $user && ($user->id === $product->user_id || $user->isAdmin());
    }

    public function create(User $user): bool
    {
        // FIX: a suspended vendor (is_active = false, toggled from the admin
        // back-office) could still publish new products — only is_approved
        // was checked here.
        return ($user->isVendeur() && $user->is_approved && $user->is_active) || $user->isAdmin();
    }

    public function update(User $user, Product $product): bool
    {
        // FIX: same gap as create() — a suspended vendor could still edit
        // their existing listings.
        return ($user->id === $product->user_id && $user->is_active) || $user->isAdmin();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->id === $product->user_id || $user->isAdmin();
    }
}
