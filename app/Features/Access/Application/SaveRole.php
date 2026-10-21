<?php

declare(strict_types=1);

namespace App\Features\Access\Application;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as RoleModel;

final class SaveRole
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(?RoleModel $role, array $data): RoleModel
    {
        return DB::transaction(function () use ($role, $data): RoleModel {
            $role ??= new RoleModel;
            $previous = $role->exists ? $role->name : null;
            $builtIn = $previous !== null && Role::isBuiltIn($previous);
            $name = $builtIn ? $previous : (string) $data['name'];

            $role->name = $name;
            $role->guard_name = 'web';
            $role->save();

            if ($previous !== null && $previous !== $name) {
                User::query()->where('role', $previous)->eachById(function (User $user) use ($name): void {
                    $user->role = $name;
                    $user->save();
                });
            }

            $permissions = $name === Role::SuperAdmin->value
                ? Permission::values()
                : array_values($data['permissions'] ?? []);

            $role->syncPermissions($permissions);

            return $role;
        });
    }
}
