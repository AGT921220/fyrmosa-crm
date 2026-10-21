<?php

declare(strict_types=1);

namespace App\Features\Access\Application;

use App\Enums\Role;
use App\Exceptions\CrmRuleException;
use App\Models\User;
use Spatie\Permission\Models\Role as RoleModel;

final class DeleteRole
{
    public function __invoke(RoleModel $role): void
    {
        if (Role::isBuiltIn($role->name)) {
            throw new CrmRuleException('No se puede eliminar un rol del sistema.');
        }

        if (User::query()->where('role', $role->name)->exists()) {
            throw new CrmRuleException('Hay usuarios con este rol.');
        }

        $role->delete();
    }
}
