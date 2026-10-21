<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'sales')->update(['role' => 'vendedor']);
        DB::table('users')->where('role', 'operations')->update(['role' => 'facturacion']);

        foreach (['sales' => 'vendedor', 'operations' => 'facturacion'] as $from => $to) {
            if (DB::table('roles')->where('name', $to)->where('guard_name', 'web')->exists()) {
                continue;
            }

            DB::table('roles')->where('name', $from)->where('guard_name', 'web')->update(['name' => $to]);
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('vendedor')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'vendedor')->update(['role' => 'sales']);
        DB::table('users')->where('role', 'facturacion')->update(['role' => 'operations']);

        foreach (['vendedor' => 'sales', 'facturacion' => 'operations'] as $from => $to) {
            if (DB::table('roles')->where('name', $to)->where('guard_name', 'web')->exists()) {
                continue;
            }

            DB::table('roles')->where('name', $from)->where('guard_name', 'web')->update(['name' => $to]);
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('sales')->change();
        });
    }
};
