<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'nickname', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected static function booted(): void
    {
        static::saved(function (User $user): void {
            $role = $user->role;

            if (! is_string($role) || $role === '') {
                return;
            }

            if (! SpatieRole::query()->where('name', $role)->where('guard_name', 'web')->exists()) {
                return;
            }

            $user->syncRoles([$role]);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function role(): Attribute
    {
        return Attribute::make(
            set: function (Role|string|null $value): ?string {
                if ($value instanceof Role) {
                    return $value->value;
                }

                return $value;
            },
        );
    }

    public function hasPermission(Permission $permission): bool
    {
        if ($this->role === Role::SuperAdmin->value) {
            return true;
        }

        return $this->hasPermissionTo($permission->value);
    }

    public function roleLabel(): string
    {
        $role = (string) $this->role;

        return Role::tryFrom($role)?->label() ?? $role;
    }

    public function homeRoute(): string
    {
        $destinations = [
            [Permission::DashboardView, 'dashboard'],
            [Permission::QuotationsView, 'quotations.index'],
            [Permission::OrdersView, 'orders.index'],
            [Permission::ClientsView, 'clients.index'],
            [Permission::UsersView, 'users.index'],
            [Permission::RolesView, 'roles.index'],
        ];

        foreach ($destinations as [$permission, $name]) {
            if ($this->hasPermission($permission)) {
                return route($name);
            }
        }

        return route('dashboard');
    }
}
