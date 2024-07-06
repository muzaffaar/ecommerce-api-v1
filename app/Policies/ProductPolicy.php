<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }
    
    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return Gate::allows('admin', $user);
    }

    public function update(User $user, Product $product): bool
    {
        return Gate::allows('admin', $user);
    }

    public function delete(User $user, Product $product): bool
    {
        return Gate::allows('admin', $user);
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return Gate::allows('admin', $user);
    }

}
