<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksM2Permissions
{
    private function canAny(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    private function viewM2(User $user, string $resource): bool
    {
        return $this->canAny($user, ['view_' . $resource, 'view_any_' . $resource, 'view_letter_request', 'view_own_letter_request']);
    }

    private function manageM2(User $user, string $resource, array $businessPermissions = []): bool
    {
        return $this->canAny($user, array_merge(['create_' . $resource, 'update_' . $resource, 'delete_' . $resource, 'manage_surat'], $businessPermissions));
    }
}
