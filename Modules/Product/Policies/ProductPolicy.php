<?php

namespace Modules\Product\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Authentication\Models\User;
use Modules\Product\Models\Product;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */

    public function update(User $user, Product $product): bool
    {
        return $user->id === $product->vendor->user_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->id === $product->vendor->user_id;
    }
}
