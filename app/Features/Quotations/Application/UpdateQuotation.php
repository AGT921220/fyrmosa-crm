<?php

declare(strict_types=1);

namespace App\Features\Quotations\Application;

use App\Enums\ActivityAction;
use App\Exceptions\CrmRuleException;
use App\Features\Shared\Application\CalculateDocumentTotals;
use App\Features\Shared\Application\PartyAttributes;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateQuotation
{
    public function __construct(
        private readonly CalculateDocumentTotals $totals,
        private readonly RecordActivity $activity,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Quotation $quotation, User $user, array $data): Quotation
    {
        if (! $quotation->status->isEditable()) {
            throw new CrmRuleException('Esta cotización ya no se puede modificar.');
        }

        return DB::transaction(function () use ($quotation, $user, $data) {
            $quotation->load('items');
            $beforeItems = $this->itemSignature($quotation);
            $beforeTerms = $this->terms($quotation);
            $beforeTotal = (string) $quotation->total;
            $totals = ($this->totals)($data['items']);

            $quotation->fill(array_merge(PartyAttributes::capture((int) $data['client_id'], (int) $data['contact_id']), [
                'issued_on' => $data['issued_on'],
                'expires_on' => $data['expires_on'],
                'user_id' => $data['user_id'],
                'currency' => $data['currency'],
                'commercial_terms' => $data['commercial_terms'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'delivery_time' => $data['delivery_time'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'client_notes' => $data['client_notes'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'total' => $totals['total'],
            ]));
            $quotation->save();
            $quotation->items()->delete();
            $quotation->items()->createMany($totals['lines']);
            $quotation->load('items');

            ($this->activity)($quotation, ActivityAction::Updated, $user, 'Cotización actualizada.');

            if ($beforeItems !== $this->itemSignature($quotation)) {
                ($this->activity)($quotation, ActivityAction::PricesChanged, $user, 'Se modificaron conceptos o precios.', [
                    'before_total' => $beforeTotal,
                    'after_total' => (string) $quotation->total,
                ]);
            }

            if ($beforeTerms !== $this->terms($quotation)) {
                ($this->activity)($quotation, ActivityAction::TermsChanged, $user, 'Se actualizaron las condiciones comerciales.');
            }

            return $quotation;
        });
    }

    /**
     * @return list<array<int, string>>
     */
    private function itemSignature(Quotation $quotation): array
    {
        return $quotation->items->map(fn (QuotationItem $item) => [
            (string) $item->description,
            (string) $item->quantity,
            (string) $item->unit_price,
            $item->discount_type->value,
            (string) $item->discount_value,
            (string) $item->tax_rate,
        ])->all();
    }

    /**
     * @return array<string, string|null>
     */
    private function terms(Quotation $quotation): array
    {
        return [
            'commercial_terms' => $quotation->commercial_terms,
            'payment_terms' => $quotation->payment_terms,
            'delivery_time' => $quotation->delivery_time,
        ];
    }
}
