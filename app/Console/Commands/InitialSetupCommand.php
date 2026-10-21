<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;

class InitialSetupCommand extends Command
{
    protected $signature = 'initial_setup';

    protected $description = 'Restaura permisos y roles de sistema, y asegura el usuario dueño (superadmin)';

    public function handle(): int
    {
        app(PermissionSeeder::class)->run();

        $this->info('Permisos restaurados: '.count(Permission::cases()));
        $this->info('Roles de sistema restaurados: '.count(Role::cases()));

        $created = $this->ensureSuperAdmin();

        if ($created) {
            $this->info('Usuario dueño creado: nickname=superadmin (password inicial: admin)');

            return self::SUCCESS;
        }

        $this->info('Usuario dueño ya existía: nickname=superadmin (contraseña no modificada)');

        return self::SUCCESS;
    }

    private function ensureSuperAdmin(): bool
    {
        $user = User::query()->firstOrCreate(
            ['nickname' => 'superadmin'],
            [
                'name' => 'Superadmin',
                'email' => 'superadmin@fyrmosa.local',
                'password' => 'admin',
                'role' => Role::SuperAdmin,
            ],
        );

        return $user->wasRecentlyCreated;
    }
}
