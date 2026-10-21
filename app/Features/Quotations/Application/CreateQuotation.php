<?php

declare(strict_types=1);

namespace App\Features\Quotations\Application;

use App\Enums\ActivityAction;
use App\Enums\QuotationStatus;
use App\Features\Shared\Application\CalculateDocumentTotals;
use App\Features\Shared\Application\NextFolio;
use App\Features\Shared\Application\PartyAttributes;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Quotation;
use App\Models\User;
use App\Support\Folio;
use Illuminate\Support\Facades\DB;

final class CreateQuotation
{
    public function __construct(
        private readonly CalculateDocumentTotals $totals,
        private readonly NextFolio $folios,
        private readonly RecordActivity $activity,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(User $user, array $data): Quotation
    {
        return DB::transaction(function () use ($user, $data) {
            $number = ($this->folios)('COT');
            $totals = ($this->totals)($data['items']);

            $quotation = Quotation::query()->create(array_merge(PartyAttributes::capture((int) $data['client_id'], (int) $data['contact_id']), [
                'folio' => Folio::format('COT', $number),
                'sequence_number' => $number,
                'version_number' => 1,
                'issued_on' => $data['issued_on'],
                'expires_on' => $data['expires_on'],
                'user_id' => $data['user_id'],
                'currency' => $data['currency'],
                'commercial_terms' => $data['commercial_terms'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'delivery_time' => $data['delivery_time'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'client_notes' => $data['client_notes'] ?? null,
                'status' => QuotationStatus::Draft,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'total' => $totals['total'],
            ]));

            $quotation->items()->createMany($totals['lines']);
            ($this->activity)($quotation, ActivityAction::Created, $user, 'Cotización creada.');

            return $quotation->load('items');
        });
    }
}
