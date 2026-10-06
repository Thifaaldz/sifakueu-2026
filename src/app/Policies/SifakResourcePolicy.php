<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class SifakResourcePolicy
{
    use HandlesAuthorization;

    protected string $resource;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_' . $this->resource);
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->can('view_' . $this->resource);
    }

    public function create(User $user): bool
    {
        return $user->can('create_' . $this->resource);
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->can('update_' . $this->resource);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->can('delete_' . $this->resource);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_' . $this->resource);
    }
}
