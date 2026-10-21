<?php

declare(strict_types=1);

namespace App\Features\Access\Application;

use App\Enums\Role;
use App\Exceptions\CrmRuleException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class SaveUser
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(?User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user ??= new User;
            $previous = $user->exists ? (string) $user->getRawOriginal('role') : null;
            $next = (string) $data['role'];

            if ($previous === Role::SuperAdmin->value && $next !== Role::SuperAdmin->value && ! $this->anotherSuperAdmin($user)) {
                throw new CrmRuleException('Debe existir al menos un superadmin.');
            }

            $user->name = $data['name'];
            $user->nickname = $data['nickname'];
            $user->email = $data['email'];
            $user->role = $next;

            $password = (string) ($data['password'] ?? '');

            if ($password !== '') {
                $user->password = $password;
            }

            if (! $user->exists && $password === '') {
                throw new CrmRuleException('La contraseña es obligatoria.');
            }

            $user->save();
            $user->syncPermissions($data['permissions'] ?? []);

            return $user;
        });
    }

    private function anotherSuperAdmin(User $user): bool
    {
        return User::query()
            ->where('role', Role::SuperAdmin->value)
            ->whereKeyNot($user->id)
            ->exists();
    }
}
