<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::RolesView);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission(Permission::RolesView);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::RolesCreate);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission(Permission::RolesUpdate);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission(Permission::RolesDelete);
    }
}
