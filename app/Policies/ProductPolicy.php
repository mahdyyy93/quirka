<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('products.view') && ($user->can('products.view-all') || $product->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.edit') && ($user->can('products.view-all') || $product->user_id === $user->id);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('products.delete') && ($user->can('products.view-all') || $product->user_id === $user->id);
    }

    public function toggleAvailability(User $user, Product $product): bool
    {
        return ($user->can('products.toggle-availability') || $user->can('products.edit'))
            && ($user->can('products.view-all') || $product->user_id === $user->id);
    }
}
