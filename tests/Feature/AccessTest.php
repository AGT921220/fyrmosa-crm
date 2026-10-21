<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\CreatesCrmUsers;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use CreatesCrmUsers;
    use RefreshDatabase;

    public function test_admin_operates_the_crm_and_cannot_manage_access(): void
    {
        $admin = $this->userWithRole(Role::Admin);

        $this->actingAs($admin)->get(route('quotations.index'))->assertOk();
        $this->actingAs($admin)->get(route('orders.index'))->assertOk();
        $this->actingAs($admin)->get(route('clients.index'))->assertOk();
        $this->actingAs($admin)->get(route('users.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('roles.store'), [
            'name' => 'almacen',
            'permissions' => ['clients.view'],
        ])->assertForbidden();
    }

    public function test_superadmin_creates_a_role_and_assigns_it_to_a_user(): void
    {
        $superadmin = $this->userWithRole(Role::SuperAdmin);

        $this->actingAs($superadmin)->post(route('roles.store'), [
            'name' => 'Almacén',
            'permissions' => ['clients.view'],
        ])->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('roles', ['name' => 'almacen']);

        $this->actingAs($superadmin)->post(route('users.store'), [
            'name' => 'Ana Ruiz',
            'nickname' => 'ana',
            'email' => 'ana@fyrmosa.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'almacen',
        ])->assertRedirect(route('users.index'));

        $ana = User::query()->where('nickname', 'ana')->first();
        $this->assertNotNull($ana);
        $this->assertTrue($ana->hasRole('almacen'));

        $this->actingAs($ana)->get(route('clients.index'))->assertOk();
        $this->actingAs($ana)->get(route('quotations.index'))->assertForbidden();
        $this->actingAs($ana)->get(route('users.index'))->assertForbidden();
        $this->actingAs($ana)->get(route('dashboard'))->assertForbidden();

        $vendedor = RoleModel::findByName('vendedor');
        $this->actingAs($superadmin)
            ->from(route('roles.index'))
            ->delete(route('roles.destroy', $vendedor))
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'vendedor']);
    }

    public function test_direct_permission_adds_an_action_the_role_does_not_have(): void
    {
        $superadmin = $this->userWithRole(Role::SuperAdmin);
        $client = Client::query()->create(['name' => 'Norte', 'legal_name' => 'Norte SA']);

        $this->actingAs($superadmin)->post(route('users.store'), [
            'name' => 'Luis Pérez',
            'nickname' => 'luis',
            'email' => 'luis@fyrmosa.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'facturacion',
            'permissions' => ['clients.update'],
        ])->assertRedirect(route('users.index'));

        $luis = User::query()->where('nickname', 'luis')->first();
        $this->actingAs($luis)->get(route('clients.edit', $client))->assertOk();
        $this->actingAs($luis)->get(route('quotations.create'))->assertForbidden();
    }

    public function test_renaming_a_custom_role_keeps_the_assigned_user(): void
    {
        $superadmin = $this->userWithRole(Role::SuperAdmin);

        $this->actingAs($superadmin)->post(route('roles.store'), [
            'name' => 'almacen',
            'permissions' => ['clients.view'],
        ]);
        $this->actingAs($superadmin)->post(route('users.store'), [
            'name' => 'Ana Ruiz',
            'nickname' => 'ana',
            'email' => 'ana@fyrmosa.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'almacen',
        ]);

        $role = RoleModel::findByName('almacen');
        $this->actingAs($superadmin)->put(route('roles.update', $role), [
            'name' => 'bodega',
            'permissions' => ['clients.view'],
        ])->assertRedirect(route('roles.index'));

        $ana = User::query()->where('nickname', 'ana')->first();
        $this->assertNotNull($ana);
        $this->assertSame('bodega', $ana->role);
        $this->assertTrue($ana->hasRole('bodega'));
        $this->assertFalse($ana->hasRole('almacen'));
    }

    public function test_owner_is_hidden_and_cannot_be_changed_or_deleted(): void
    {
        $this->seed(DatabaseSeeder::class);
        $actor = $this->userWithRole(Role::SuperAdmin);
        $owner = User::query()->where('nickname', 'superadmin')->firstOrFail();

        $this->actingAs($actor)
            ->get(route('users.index'))
            ->assertOk()
            ->assertDontSee($owner->email)
            ->assertDontSee('>'.$owner->nickname.'<', false);

        $this->actingAs($actor)->get(route('users.edit', $owner))->assertNotFound();
        $this->actingAs($actor)->put(route('users.update', $owner), [
            'name' => 'Hackeado',
            'nickname' => 'superadmin',
            'email' => $owner->email,
            'role' => Role::Admin->value,
        ])->assertNotFound();
        $this->actingAs($actor)->delete(route('users.destroy', $owner))->assertNotFound();

        $owner->refresh();
        $this->assertSame('Superadmin', $owner->name);
        $this->assertSame(Role::SuperAdmin->value, $owner->role);
        $this->assertDatabaseHas('users', ['nickname' => 'superadmin']);
    }

    public function test_seeder_creates_the_owner_without_rewriting_passwords(): void
    {
        $admin = User::factory()->create([
            'name' => 'Administrador',
            'nickname' => 'admin',
            'email' => 'admin@fyrmosa.local',
            'password' => 'clave-admin',
            'role' => Role::Admin,
        ]);

        $this->seed(DatabaseSeeder::class);

        $admin->refresh();
        $this->assertSame(Role::Admin->value, $admin->role);
        $this->assertTrue(Hash::check('clave-admin', $admin->password));

        $owner = User::query()->where('nickname', 'superadmin')->first();
        $this->assertNotNull($owner);
        $this->assertSame(Role::SuperAdmin->value, $owner->role);
        $this->assertTrue($owner->hasRole(Role::SuperAdmin->value));
        $this->assertTrue(Hash::check('admin', $owner->password));

        $owner->password = 'clave-dueno';
        $owner->save();

        $this->seed(DatabaseSeeder::class);

        $owner->refresh();
        $admin->refresh();
        $this->assertTrue(Hash::check('clave-dueno', $owner->password));
        $this->assertSame(Role::Admin->value, $admin->role);
        $this->assertTrue(Hash::check('clave-admin', $admin->password));
    }
}
