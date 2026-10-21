<?php

declare(strict_types=1);

namespace App\Features\Access\Application;

use App\Enums\Role;
use App\Exceptions\CrmRuleException;
use App\Models\User;

final class DeleteUser
{
    public function __invoke(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw new CrmRuleException('No puedes eliminar tu propio usuario.');
        }

        if ($user->role === Role::SuperAdmin->value && User::query()->where('role', Role::SuperAdmin->value)->count() <= 1) {
            throw new CrmRuleException('Debe existir al menos un superadmin.');
        }

        $user->delete();
    }
}
