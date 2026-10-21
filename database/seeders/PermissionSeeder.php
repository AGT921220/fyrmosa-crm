<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\Role;
use App\Support\RolePermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permission::cases() as $permission) {
            PermissionModel::findOrCreate($permission->value, 'web');
        }

        foreach (Role::cases() as $role) {
            $model = RoleModel::findOrCreate($role->value, 'web');
            $model->syncPermissions(array_map(
                fn (Permission $permission): string => $permission->value,
                RolePermissions::for($role),
            ));
        }
    }
}
