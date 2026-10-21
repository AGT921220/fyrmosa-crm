<?php

declare(strict_types=1);

namespace App\Features\Quotations\Application;

use App\Models\Quotation;
use App\Models\QuotationVersion;
use App\Models\User;

final class SnapshotQuotation
{
    public function __invoke(Quotation $quotation, ?User $user): QuotationVersion
    {
        $quotation->loadMissing('items');

        return $quotation->versions()->create([
            'version_number' => $quotation->version_number,
            'user_id' => $user?->id,
            'snapshot' => [
                'folio' => $quotation->folio,
                'version_number' => $quotation->version_number,
                'status' => $quotation->status->value,
                'currency' => $quotation->currency->value,
                'client_name' => $quotation->client_name,
                'legal_name' => $quotation->legal_name,
                'contact_first_name' => $quotation->contact_first_name,
                'contact_last_name' => $quotation->contact_last_name,
                'email' => $quotation->email,
                'phone' => $quotation->phone,
                'issued_on' => $quotation->issued_on?->toDateString(),
                'expires_on' => $quotation->expires_on?->toDateString(),
                'commercial_terms' => $quotation->commercial_terms,
                'payment_terms' => $quotation->payment_terms,
                'delivery_time' => $quotation->delivery_time,
                'client_notes' => $quotation->client_notes,
                'subtotal' => (string) $quotation->subtotal,
                'discount_total' => (string) $quotation->discount_total,
                'tax_total' => (string) $quotation->tax_total,
                'total' => (string) $quotation->total,
                'items' => $quotation->items->map(fn ($item) => [
                    'sku' => $item->sku,
                    'description' => $item->description,
                    'quantity' => (string) $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => (string) $item->unit_price,
                    'discount_type' => $item->discount_type->value,
                    'discount_value' => (string) $item->discount_value,
                    'discount_amount' => (string) $item->discount_amount,
                    'tax_rate' => (string) $item->tax_rate,
                    'subtotal' => (string) $item->subtotal,
                    'tax_amount' => (string) $item->tax_amount,
                    'total' => (string) $item->total,
                ])->values()->all(),
            ],
        ]);
    }
}
