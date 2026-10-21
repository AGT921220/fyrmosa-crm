<?php

declare(strict_types=1);

namespace App\Features\Orders\Application;

use App\Enums\ActivityAction;
use App\Exceptions\CrmRuleException;
use App\Features\Shared\Application\CalculateDocumentTotals;
use App\Features\Shared\Application\PartyAttributes;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateOrder
{
    public function __construct(
        private readonly CalculateDocumentTotals $totals,
        private readonly RecordActivity $activity,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Order $order, User $user, array $data): Order
    {
        if (! $order->status->isEditable()) {
            throw new CrmRuleException('Este pedido ya no se puede modificar.');
        }

        return DB::transaction(function () use ($order, $user, $data) {
            $order->load('items');
            $beforeItems = $this->itemSignature($order);
            $beforeTerms = [$order->commercial_terms, $order->payment_terms];
            $beforeTotal = (string) $order->total;
            $totals = ($this->totals)($data['items']);

            $order->fill(array_merge(PartyAttributes::capture((int) $data['client_id'], (int) $data['contact_id']), [
                'client_folio' => $data['client_folio'] ?? null,
                'issued_on' => $data['issued_on'],
                'estimated_delivery_on' => $data['estimated_delivery_on'] ?? null,
                'user_id' => $data['user_id'],
                'currency' => $data['currency'],
                'commercial_terms' => $data['commercial_terms'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'total' => $totals['total'],
            ]));
            $order->save();
            $order->items()->delete();
            $order->items()->createMany(array_map(function (array $line) {
                $line['quantity_delivered'] = 0;

                return $line;
            }, $totals['lines']));
            $order->load('items');

            ($this->activity)($order, ActivityAction::Updated, $user, 'Pedido actualizado.');

            if ($beforeItems !== $this->itemSignature($order)) {
                ($this->activity)($order, ActivityAction::PricesChanged, $user, 'Se modificaron conceptos o precios.', [
                    'before_total' => $beforeTotal,
                    'after_total' => (string) $order->total,
                ]);
            }

            if ($beforeTerms !== [$order->commercial_terms, $order->payment_terms]) {
                ($this->activity)($order, ActivityAction::TermsChanged, $user, 'Se actualizaron las condiciones.');
            }

            return $order;
        });
    }

    /**
     * @return list<array<int, string>>
     */
    private function itemSignature(Order $order): array
    {
        return $order->items->map(fn (OrderItem $item) => [
            (string) $item->description,
            (string) $item->quantity,
            (string) $item->unit_price,
            $item->discount_type->value,
            (string) $item->discount_value,
            (string) $item->tax_rate,
        ])->all();
    }
}
