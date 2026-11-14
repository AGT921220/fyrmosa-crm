<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateBulkPasswords extends Command
{
    protected $signature = 'update-bulk-passwords';
    protected $description = "Actualiza todas lsa contraseñas(Sólo ambiente de desarrollo)";
    public function handle()
    {
        if (app()->environment('local')|| app()->environment('staging')) {
            $this->info('Updating passwords in local environment.');
            User::query()->update(['password' => bcrypt('admin')]);
        }
    }
}
