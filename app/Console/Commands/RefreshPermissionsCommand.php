<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Permission;
use App\Enums\Role;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;

class RefreshPermissionsCommand extends Command
{
    protected $signature = 'permissions:refresh';

    protected $description = 'Restaura los permisos y los cuatro roles de sistema';

    public function handle(): int
    {
        app(PermissionSeeder::class)->run();

        $this->info('Permisos restaurados: '.count(Permission::cases()));
        $this->info('Roles de sistema restaurados: '.count(Role::cases()));

        return self::SUCCESS;
    }
}
