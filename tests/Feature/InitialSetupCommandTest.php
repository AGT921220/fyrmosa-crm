<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

class InitialSetupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_setup_restores_permissions_roles_and_creates_only_superadmin(): void
    {
        $this->artisan('initial_setup')
            ->expectsOutputToContain('Permisos restaurados')
            ->expectsOutputToContain('Usuario dueño creado')
            ->assertSuccessful();

        $this->assertSame(count(Permission::cases()), PermissionModel::query()->count());

        foreach (Role::cases() as $role) {
            $this->assertTrue(RoleModel::query()->where('name', $role->value)->where('guard_name', 'web')->exists());
        }

        $owner = User::query()->where('nickname', 'superadmin')->first();
        $this->assertNotNull($owner);
        $this->assertSame(Role::SuperAdmin->value, $owner->role);
        $this->assertTrue($owner->hasRole(Role::SuperAdmin->value));
        $this->assertTrue(Hash::check('admin', $owner->password));

        $this->assertDatabaseMissing('users', ['nickname' => 'admin']);
    }

    public function test_initial_setup_is_idempotent_and_does_not_rewrite_password(): void
    {
        $this->artisan('initial_setup')->assertSuccessful();

        $owner = User::query()->where('nickname', 'superadmin')->firstOrFail();
        $owner->password = 'clave-dueno';
        $owner->save();

        $this->artisan('initial_setup')
            ->expectsOutputToContain('Usuario dueño ya existía')
            ->assertSuccessful();

        $owner->refresh();
        $this->assertTrue(Hash::check('clave-dueno', $owner->password));
        $this->assertDatabaseMissing('users', ['nickname' => 'admin']);
        $this->assertSame(1, User::query()->count());
    }

    public function test_permissions_refresh_syncs_system_roles_and_keeps_custom_ones(): void
    {
        $this->artisan('permissions:refresh')->assertSuccessful();

        PermissionModel::query()->create([
            'name' => 'obsolete.permission',
            'guard_name' => 'web',
        ]);

        $admin = RoleModel::findByName(Role::Admin->value);
        $admin->revokePermissionTo(Permission::QuotationsOverrideStatus->value);

        $custom = RoleModel::query()->create([
            'name' => 'supervisor',
            'guard_name' => 'web',
        ]);
        $custom->givePermissionTo(Permission::ClientsView->value);

        $this->artisan('permissions:refresh')
            ->expectsOutputToContain('Permisos restaurados: '.count(Permission::cases()))
            ->expectsOutputToContain('Roles de sistema restaurados: '.count(Role::cases()))
            ->assertSuccessful();

        $this->assertDatabaseMissing('permissions', ['name' => 'obsolete.permission']);
        $this->assertTrue(
            RoleModel::findByName(Role::Admin->value)->hasPermissionTo(Permission::QuotationsOverrideStatus->value),
        );
        $this->assertFalse(
            RoleModel::findByName(Role::Vendedor->value)->hasPermissionTo(Permission::QuotationsOverrideStatus->value),
        );
        $this->assertTrue(
            RoleModel::findByName('supervisor')->hasPermissionTo(Permission::ClientsView->value),
        );
        $this->assertSame(0, User::query()->count());
    }
}
