<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ClientsView);
    }

    public function view(User $user, Client $client): bool
    {
        return $user->hasPermission(Permission::ClientsView);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ClientsCreate);
    }

    public function update(User $user, Client $client): bool
    {
        return $user->hasPermission(Permission::ClientsUpdate);
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->hasPermission(Permission::ClientsDelete);
    }
}
