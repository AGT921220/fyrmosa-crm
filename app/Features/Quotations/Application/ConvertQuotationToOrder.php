<?php

declare(strict_types=1);

namespace App\Features\Quotations\Application;

use App\Enums\ActivityAction;
use App\Enums\OrderStatus;
use App\Enums\QuotationStatus;
use App\Exceptions\CrmRuleException;
use App\Features\Shared\Application\NextFolio;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\User;
use App\Support\Folio;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final class ConvertQuotationToOrder
{
    public function __construct(
        private readonly NextFolio $folios,
        private readonly RecordActivity $activity,
    ) {}

    public function __invoke(Quotation $quotation, User $user): Order
    {
        if ($quotation->status !== QuotationStatus::Approved) {
            throw new CrmRuleException('Solo se puede convertir una cotización aprobada.');
        }

        return DB::transaction(function () use ($quotation, $user) {
            $rootId = $quotation->root_id ?? $quotation->id;
            $ids = Quotation::query()
                ->where('id', $rootId)
                ->orWhere('root_id', $rootId)
                ->lockForUpdate()
                ->pluck('id');

            $exists = Order::query()
                ->whereIn('quotation_id', $ids)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                throw new CrmRuleException('Esta cotización ya tiene un pedido.');
            }

            $quotation->load('items');
            $number = ($this->folios)('PED');

            try {
                $order = Order::query()->create([
                    'folio' => Folio::format('PED', $number),
                    'sequence_number' => $number,
                    'quotation_id' => $quotation->id,
                    'quotation_version' => $quotation->version_number,
                    'client_id' => $quotation->client_id,
                    'contact_id' => $quotation->contact_id,
                    'client_name' => $quotation->client_name,
                    'legal_name' => $quotation->legal_name,
                    'contact_first_name' => $quotation->contact_first_name,
                    'contact_last_name' => $quotation->contact_last_name,
                    'email' => $quotation->email,
                    'phone' => $quotation->phone,
                    'issued_on' => now()->toDateString(),
                    'estimated_delivery_on' => null,
                    'user_id' => $quotation->user_id,
                    'converted_by' => $user->id,
                    'converted_at' => now(),
                    'currency' => $quotation->currency,
                    'commercial_terms' => $quotation->commercial_terms,
                    'payment_terms' => $quotation->payment_terms,
                    'notes' => $quotation->client_notes,
                    'status' => OrderStatus::PendingConfirmation,
                    'subtotal' => $quotation->subtotal,
                    'discount_total' => $quotation->discount_total,
                    'tax_total' => $quotation->tax_total,
                    'total' => $quotation->total,
                ]);
            } catch (UniqueConstraintViolationException) {
                throw new CrmRuleException('Esta cotización ya tiene un pedido.');
            }

            $order->items()->createMany($quotation->items->map(fn ($item) => [
                'sku' => $item->sku,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'quantity_delivered' => 0,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'discount_type' => $item->discount_type,
                'discount_value' => $item->discount_value,
                'discount_amount' => $item->discount_amount,
                'tax_rate' => $item->tax_rate,
                'subtotal' => $item->subtotal,
                'tax_amount' => $item->tax_amount,
                'total' => $item->total,
                'position' => $item->position,
            ])->all());

            ($this->activity)($quotation, ActivityAction::Converted, $user, 'Convertida al pedido '.$order->folio.'.', [
                'order_id' => $order->id,
            ]);
            ($this->activity)($order, ActivityAction::Created, $user, 'Generada desde la cotización '.$quotation->folio.'.');

            return $order->load('items');
        });
    }
}
