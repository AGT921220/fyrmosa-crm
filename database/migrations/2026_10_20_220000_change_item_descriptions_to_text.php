<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['quotation_items', 'order_items'] as $table) {
            if (! $this->descriptionIsVarchar($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->text('description')->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['quotation_items', 'order_items'] as $table) {
            if (! $this->descriptionIsText($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('description', 1000)->change();
            });
        }
    }

    private function descriptionIsVarchar(string $table): bool
    {
        $type = $this->descriptionType($table);

        return $type !== null && (str_contains($type, 'varchar') || str_contains($type, 'character varying') || $type === 'string');
    }

    private function descriptionIsText(string $table): bool
    {
        $type = $this->descriptionType($table);

        return $type === 'text';
    }

    private function descriptionType(string $table): ?string
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'description')) {
            return null;
        }

        $column = collect(Schema::getColumns($table))->firstWhere('name', 'description');

        if (! is_array($column)) {
            return null;
        }

        return strtolower((string) ($column['type_name'] ?? $column['type'] ?? ''));
    }
};
