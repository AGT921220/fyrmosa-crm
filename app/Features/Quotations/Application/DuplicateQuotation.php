<?php

declare(strict_types=1);

namespace App\Features\Quotations\Application;

use App\Enums\ActivityAction;
use App\Enums\QuotationStatus;
use App\Features\Shared\Application\NextFolio;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Quotation;
use App\Models\User;
use App\Support\Folio;
use Illuminate\Support\Facades\DB;

final class DuplicateQuotation
{
    public function __construct(
        private readonly NextFolio $folios,
        private readonly RecordActivity $activity,
    ) {}

    public function __invoke(Quotation $source, User $user): Quotation
    {
        return DB::transaction(function () use ($source, $user) {
            $source->load('items');
            $number = ($this->folios)('COT');

            $copy = Quotation::query()->create([
                'folio' => Folio::format('COT', $number),
                'sequence_number' => $number,
                'version_number' => 1,
                'client_id' => $source->client_id,
                'contact_id' => $source->contact_id,
                'client_name' => $source->client_name,
                'legal_name' => $source->legal_name,
                'contact_first_name' => $source->contact_first_name,
                'contact_last_name' => $source->contact_last_name,
                'email' => $source->email,
                'phone' => $source->phone,
                'issued_on' => now()->toDateString(),
                'expires_on' => now()->addDays(15)->toDateString(),
                'user_id' => $source->user_id,
                'currency' => $source->currency,
                'commercial_terms' => $source->commercial_terms,
                'payment_terms' => $source->payment_terms,
                'delivery_time' => $source->delivery_time,
                'internal_notes' => $source->internal_notes,
                'client_notes' => $source->client_notes,
                'status' => QuotationStatus::Draft,
                'subtotal' => $source->subtotal,
                'discount_total' => $source->discount_total,
                'tax_total' => $source->tax_total,
                'total' => $source->total,
            ]);

            $copy->items()->createMany($source->items->map(fn ($item) => [
                'sku' => $item->sku,
                'description' => $item->description,
                'quantity' => $item->quantity,
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

            ($this->activity)($copy, ActivityAction::Created, $user, 'Cotización duplicada desde '.$source->folio.'.');
            ($this->activity)($source, ActivityAction::Duplicated, $user, 'Se generó el duplicado '.$copy->folio.'.', [
                'duplicate_id' => $copy->id,
            ]);

            return $copy;
        });
    }
}
