<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameLegacyTables();

        if (! Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->cascadeOnDelete();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone', 40);
                $table->string('email');
                $table->timestamps();
            });
        }

        foreach (['quotations', 'orders'] as $table) {
            $this->addPartyColumns($table);
        }

        if (Schema::hasColumn('clients', 'contact_name')) {
            $this->backfillContactsFromClients();
        }

        foreach (['quotations', 'orders'] as $table) {
            $this->linkDocuments($table);
            $this->finalizePartyColumns($table);
        }

        if (Schema::hasColumn('clients', 'contact_name')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn(['contact_name', 'email', 'phone']);
            });
        }

        foreach (['activity_logs' => 'subject_type', 'follow_ups' => 'followable_type', 'attachments' => 'attachable_type'] as $table => $column) {
            if (Schema::hasTable($table)) {
                DB::table($table)->where($column, 'purchase_order')->update([$column => 'order']);
            }
        }

        foreach ([
            'purchase_orders.view' => 'orders.view',
            'purchase_orders.create' => 'orders.create',
            'purchase_orders.update' => 'orders.update',
            'purchase_orders.confirm' => 'orders.confirm',
            'purchase_orders.cancel' => 'orders.cancel',
        ] as $from => $to) {
            if (Schema::hasTable('permissions') && ! DB::table('permissions')->where('name', $to)->exists()) {
                DB::table('permissions')->where('name', $from)->update(['name' => $to]);
            }
        }

        if (Schema::hasTable('folio_sequences')
            && DB::table('folio_sequences')->where('prefix', 'OC')->exists()
            && ! DB::table('folio_sequences')->where('prefix', 'PED')->exists()) {
            DB::table('folio_sequences')->where('prefix', 'OC')->update(['prefix' => 'PED']);
        }

        if (Schema::hasTable('orders')) {
            foreach (DB::table('orders')->where('folio', 'like', 'OC-%')->get(['id', 'folio']) as $row) {
                DB::table('orders')->where('id', $row->id)->update([
                    'folio' => 'PED'.substr((string) $row->folio, 2),
                ]);
            }
        }
    }

    public function down(): void
    {
        // El esquema anterior ya no es el contrato del módulo.
    }

    private function renameLegacyTables(): void
    {
        if (! Schema::hasTable('purchase_orders')) {
            return;
        }

        Schema::table('purchase_order_delivery_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_delivery_id']);
            $table->dropForeign(['purchase_order_item_id']);
        });
        Schema::table('purchase_order_deliveries', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
        });
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
        });

        Schema::rename('purchase_orders', 'orders');
        Schema::rename('purchase_order_items', 'order_items');
        Schema::rename('purchase_order_deliveries', 'order_deliveries');
        Schema::rename('purchase_order_delivery_items', 'order_delivery_items');

        Schema::table('order_items', function (Blueprint $table) {
            $table->renameColumn('purchase_order_id', 'order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->renameColumn('purchase_order_id', 'order_id');
            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete();
        });
        Schema::table('order_delivery_items', function (Blueprint $table) {
            $table->renameColumn('purchase_order_delivery_id', 'order_delivery_id');
            $table->renameColumn('purchase_order_item_id', 'order_item_id');
            $table->foreign('order_delivery_id')->references('id')->on('order_deliveries')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->restrictOnDelete();
        });
    }

    private function addPartyColumns(string $table): void
    {
        if (! Schema::hasColumn($table, 'contact_id')) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('contact_id')->nullable()->after('client_id')->constrained()->restrictOnDelete();
            });
        }

        if (! Schema::hasColumn($table, 'contact_first_name')) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('contact_first_name')->nullable()->after('legal_name');
                $table->string('contact_last_name')->nullable()->after('contact_first_name');
            });
        }
    }

    private function backfillContactsFromClients(): void
    {
        foreach (DB::table('clients')->get() as $client) {
            if (DB::table('contacts')->where('client_id', $client->id)->exists()) {
                continue;
            }

            [$first, $last] = $this->splitName($client->contact_name ?: $client->name);
            DB::table('contacts')->insert([
                'client_id' => $client->id,
                'first_name' => $first,
                'last_name' => $last,
                'phone' => $client->phone ?: '-',
                'email' => $client->email ?: 'contacto'.$client->id.'@local.test',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function linkDocuments(string $table): void
    {
        if (! Schema::hasColumn($table, 'contact_id')) {
            return;
        }

        foreach (DB::table($table)->whereNull('contact_id')->get() as $row) {
            if ($row->client_id === null) {
                throw new RuntimeException('El registro '.$table.' #'.$row->id.' no tiene cliente.');
            }

            $contactId = DB::table('contacts')->where('client_id', $row->client_id)->value('id');
            if ($contactId === null) {
                [$first, $last] = $this->splitName($row->contact_name ?? $row->client_name);
                $contactId = DB::table('contacts')->insertGetId([
                    'client_id' => $row->client_id,
                    'first_name' => $first,
                    'last_name' => $last,
                    'phone' => $row->phone ?: '-',
                    'email' => $row->email ?: 'contacto'.$row->client_id.'@local.test',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $contact = DB::table('contacts')->where('id', $contactId)->first();
            DB::table($table)->where('id', $row->id)->update([
                'contact_id' => $contact->id,
                'contact_first_name' => $contact->first_name,
                'contact_last_name' => $contact->last_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
            ]);
        }
    }

    private function finalizePartyColumns(string $table): void
    {
        $columns = collect(Schema::getColumns($table))->keyBy('name');

        if (Schema::hasColumn($table, 'contact_name')) {
            foreach (DB::table($table)->whereNull('contact_first_name')->get() as $row) {
                [$first, $last] = $this->splitName($row->contact_name ?? '');
                DB::table($table)->where('id', $row->id)->update([
                    'contact_first_name' => $first,
                    'contact_last_name' => $last,
                ]);
            }

            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('contact_name');
            });
            $columns = collect(Schema::getColumns($table))->keyBy('name');
        }

        $nullable = collect(['client_id', 'contact_id', 'contact_first_name', 'contact_last_name', 'email', 'phone'])
            ->filter(fn (string $name) => (bool) ($columns[$name]['nullable'] ?? false));

        if ($nullable->isEmpty()) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($nullable) {
            if ($nullable->contains('client_id')) {
                $blueprint->unsignedBigInteger('client_id')->nullable(false)->change();
            }
            if ($nullable->contains('contact_id')) {
                $blueprint->unsignedBigInteger('contact_id')->nullable(false)->change();
            }
            if ($nullable->contains('contact_first_name')) {
                $blueprint->string('contact_first_name')->nullable(false)->change();
            }
            if ($nullable->contains('contact_last_name')) {
                $blueprint->string('contact_last_name')->nullable(false)->change();
            }
            if ($nullable->contains('email')) {
                $blueprint->string('email')->nullable(false)->change();
            }
            if ($nullable->contains('phone')) {
                $blueprint->string('phone', 40)->nullable(false)->change();
            }
        });
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(?string $name): array
    {
        $name = trim((string) $name);
        if ($name === '') {
            return ['Contacto', '-'];
        }

        $parts = preg_split('/\s+/', $name, 2) ?: [];

        return [$parts[0], $parts[1] ?? '-'];
    }
};
