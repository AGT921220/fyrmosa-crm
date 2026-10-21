<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Features\Access\Application\DeleteRole;
use App\Features\Access\Application\SaveRole;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role as RoleModel;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', RoleModel::class);

        return view('roles.index', [
            'roles' => RoleModel::query()->withCount('permissions')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', RoleModel::class);

        return view('roles.create');
    }

    public function store(StoreRoleRequest $request, SaveRole $save): RedirectResponse
    {
        $save(null, $request->validated());

        return redirect()->route('roles.index')->with('success', 'Rol guardado.');
    }

    public function edit(RoleModel $role): View
    {
        $this->authorize('update', $role);

        return view('roles.edit', [
            'role' => $role,
            'selected' => $role->permissions->pluck('name')->all(),
            'builtIn' => Role::isBuiltIn($role->name),
        ]);
    }

    public function update(UpdateRoleRequest $request, RoleModel $role, SaveRole $save): RedirectResponse
    {
        $save($role, $request->validated());

        return redirect()->route('roles.index')->with('success', 'Rol actualizado.');
    }

    public function destroy(RoleModel $role, DeleteRole $delete): RedirectResponse
    {
        $this->authorize('delete', $role);
        $delete($role);

        return redirect()->route('roles.index')->with('success', 'Rol eliminado.');
    }
}
