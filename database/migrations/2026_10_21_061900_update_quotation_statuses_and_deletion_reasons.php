<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'borrador' => 'solicitada',
            'enviada' => 'cotizada',
            'en_seguimiento' => 'cotizada',
            'aprobada' => 'ganada',
            'vencida' => 'perdida',
            'cancelada' => 'perdida',
        ];

        foreach ($map as $from => $to) {
            DB::table('quotations')->where('status', $from)->update(['status' => $to]);
        }

        Schema::table('quotations', function (Blueprint $table) {
            $table->text('deleted_reason')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->text('deleted_reason')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropColumn('deleted_reason');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropColumn('deleted_reason');
        });

        $map = [
            'solicitada' => 'borrador',
            'cotizada' => 'enviada',
            'ganada' => 'aprobada',
            'perdida' => 'cancelada',
        ];

        foreach ($map as $from => $to) {
            DB::table('quotations')->where('status', $from)->update(['status' => $to]);
        }
    }
};
