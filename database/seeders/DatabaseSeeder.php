<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        User::query()->firstOrCreate(
            ['nickname' => 'admin'],
            [
                'name' => 'Administrador',
                'email' => 'admin@fyrmosa.local',
                'password' => 'admin',
                'role' => Role::Admin,
            ],
        );

        User::query()->firstOrCreate(
            ['nickname' => 'superadmin'],
            [
                'name' => 'Superadmin',
                'email' => 'superadmin@fyrmosa.local',
                'password' => 'admin',
                'role' => Role::SuperAdmin,
            ],
        );
    }
}
