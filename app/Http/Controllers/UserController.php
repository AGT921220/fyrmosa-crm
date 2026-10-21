<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Features\Access\Application\DeleteUser;
use App\Features\Access\Application\SaveUser;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role as RoleModel;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('users.index', [
            'users' => User::query()->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'roles' => RoleModel::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request, SaveUser $save): RedirectResponse
    {
        $save(null, $request->validated());

        return redirect()->route('users.index')->with('success', 'Usuario guardado.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => RoleModel::query()->orderBy('name')->get(),
            'directPermissions' => $user->getDirectPermissions()->pluck('name')->all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, SaveUser $save): RedirectResponse
    {
        $save($user, $request->validated());

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user, DeleteUser $delete): RedirectResponse
    {
        $this->authorize('delete', $user);
        $delete($user, $this->currentUser());

        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }

    private function currentUser(): User
    {
        $user = request()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }
}
