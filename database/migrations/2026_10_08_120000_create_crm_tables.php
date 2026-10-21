<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 40);
            $table->string('email');
            $table->timestamps();
        });

        Schema::create('folio_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 10)->unique();
            $table->unsignedInteger('current')->default(0);
            $table->timestamps();
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->unsignedInteger('sequence_number');
            $table->unsignedInteger('version_number')->default(1);
            $table->foreignId('parent_id')->nullable()->constrained('quotations')->restrictOnDelete();
            $table->foreignId('root_id')->nullable()->constrained('quotations')->restrictOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('contact_id')->constrained()->restrictOnDelete();
            $table->string('client_name');
            $table->string('legal_name')->nullable();
            $table->string('contact_first_name');
            $table->string('contact_last_name');
            $table->string('email');
            $table->string('phone', 40);
            $table->date('issued_on');
            $table->date('expires_on');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('currency', 3);
            $table->text('commercial_terms')->nullable();
            $table->text('payment_terms')->nullable();
            $table->string('delivery_time')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('client_notes')->nullable();
            $table->string('status', 40);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['sequence_number', 'version_number']);
            $table->index(['status', 'expires_on']);
            $table->index('currency');
            $table->index('issued_on');
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->text('description');
            $table->decimal('quantity', 15, 4);
            $table->string('unit', 40);
            $table->decimal('unit_price', 15, 4);
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 15, 4)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('quotation_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('snapshot');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->unsignedInteger('sequence_number');
            $table->foreignId('quotation_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->unsignedInteger('quotation_version')->nullable();
            $table->string('client_folio')->nullable();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('contact_id')->constrained()->restrictOnDelete();
            $table->string('client_name');
            $table->string('legal_name')->nullable();
            $table->string('contact_first_name');
            $table->string('contact_last_name');
            $table->string('email');
            $table->string('phone', 40);
            $table->date('issued_on');
            $table->date('estimated_delivery_on')->nullable();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->string('currency', 3);
            $table->text('commercial_terms')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 40);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('currency');
            $table->index('issued_on');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->text('description');
            $table->decimal('quantity', 15, 4);
            $table->decimal('quantity_delivered', 15, 4)->default(0);
            $table->string('unit', 40);
            $table->decimal('unit_price', 15, 4);
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 15, 4)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->date('delivered_on');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('order_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->timestamps();
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->morphs('followable');
            $table->string('type', 20);
            $table->text('result')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('next_follow_up_at')->nullable();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->index('next_follow_up_at');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            $table->string('action', 40);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('comment')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->string('disk', 40);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('order_delivery_items');
        Schema::dropIfExists('order_deliveries');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('quotation_versions');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('folio_sequences');
        Schema::dropIfExists('clients');
    }
};
