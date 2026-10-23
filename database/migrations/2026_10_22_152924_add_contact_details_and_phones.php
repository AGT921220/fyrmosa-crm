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
        Schema::table('contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('contacts', 'puesto')) {
                $table->string('puesto')->nullable()->after('last_name');
            }
            if (! Schema::hasColumn('contacts', 'comentario')) {
                $table->text('comentario')->nullable()->after('email');
            }
        });

        if (! Schema::hasTable('contact_phones')) {
            Schema::create('contact_phones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
                $table->string('phone', 40);
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('contacts', 'phone')) {
            foreach (DB::table('contacts')->select('id', 'phone')->get() as $contact) {
                if ($contact->phone === null || $contact->phone === '') {
                    continue;
                }

                if (DB::table('contact_phones')->where('contact_id', $contact->id)->exists()) {
                    continue;
                }

                DB::table('contact_phones')->insert([
                    'contact_id' => $contact->id,
                    'phone' => $contact->phone,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('contacts', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('contacts', 'phone')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->string('phone', 40)->default('-')->after('last_name');
            });
        }

        if (Schema::hasTable('contact_phones')) {
            foreach (DB::table('contacts')->select('id')->get() as $contact) {
                $phone = DB::table('contact_phones')
                    ->where('contact_id', $contact->id)
                    ->orderBy('id')
                    ->value('phone');

                DB::table('contacts')->where('id', $contact->id)->update([
                    'phone' => $phone ?: '-',
                ]);
            }

            Schema::dropIfExists('contact_phones');
        }

        Schema::table('contacts', function (Blueprint $table) {
            $drops = collect(['puesto', 'comentario'])
                ->filter(fn (string $column) => Schema::hasColumn('contacts', $column))
                ->all();

            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
