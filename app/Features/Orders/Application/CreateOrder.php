<?php

declare(strict_types=1);

namespace App\Features\Orders\Application;

use App\Enums\ActivityAction;
use App\Enums\OrderStatus;
use App\Features\Shared\Application\CalculateDocumentTotals;
use App\Features\Shared\Application\NextFolio;
use App\Features\Shared\Application\PartyAttributes;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Order;
use App\Models\User;
use App\Support\Folio;
use Illuminate\Support\Facades\DB;

final class CreateOrder
{
    public function __construct(
        private readonly CalculateDocumentTotals $totals,
        private readonly NextFolio $folios,
        private readonly RecordActivity $activity,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $number = ($this->folios)('PED');
            $totals = ($this->totals)($data['items']);

            $order = Order::query()->create(array_merge(PartyAttributes::capture((int) $data['client_id'], (int) $data['contact_id']), [
                'folio' => Folio::format('PED', $number),
                'sequence_number' => $number,
                'client_folio' => $data['client_folio'] ?? null,
                'issued_on' => $data['issued_on'],
                'estimated_delivery_on' => $data['estimated_delivery_on'] ?? null,
                'user_id' => $data['user_id'],
                'currency' => $data['currency'],
                'commercial_terms' => $data['commercial_terms'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => OrderStatus::Draft,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'total' => $totals['total'],
            ]));

            $order->items()->createMany($this->withDelivered($totals['lines']));
            ($this->activity)($order, ActivityAction::Created, $user, 'Pedido creado.');

            return $order->load('items');
        });
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function withDelivered(array $lines): array
    {
        return array_map(function (array $line) {
            $line['quantity_delivered'] = 0;

            return $line;
        }, $lines);
    }
}
