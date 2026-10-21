<?php

declare(strict_types=1);

namespace App\Features\Orders\Application;

use App\Enums\ActivityAction;
use App\Enums\OrderStatus;
use App\Exceptions\CrmRuleException;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

final class RegisterDelivery
{
    public function __construct(private readonly RecordActivity $activity) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Order $order, User $user, array $data): OrderDelivery
    {
        if (! $order->status->acceptsDeliveries()) {
            throw new CrmRuleException('No se pueden registrar entregas en un pedido '.$order->status->label().'.');
        }

        $lines = collect($data['items'] ?? [])
            ->filter(fn (array $line) => Money::gt(Money::of($line['quantity'] ?? 0, 4), '0'))
            ->values();

        if ($lines->isEmpty()) {
            throw new CrmRuleException('Indica al menos una cantidad a entregar.');
        }

        return DB::transaction(function () use ($order, $user, $data, $lines) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if ($locked === null || ! $locked->status->acceptsDeliveries()) {
                throw new CrmRuleException('No se pueden registrar entregas en un pedido cancelado o cerrado.');
            }

            $delivery = $locked->deliveries()->create([
                'delivered_on' => $data['delivered_on'],
                'notes' => $data['notes'] ?? null,
                'user_id' => $user->id,
            ]);

            foreach ($lines as $line) {
                $item = OrderItem::query()
                    ->whereKey($line['order_item_id'])
                    ->where('order_id', $locked->id)
                    ->lockForUpdate()
                    ->first();

                if ($item === null) {
                    throw new CrmRuleException('El concepto no pertenece al pedido.');
                }

                $quantity = Money::of($line['quantity'], 4);
                $remaining = Money::sub((string) $item->quantity, (string) $item->quantity_delivered, 4);

                if (Money::gt($quantity, $remaining)) {
                    throw new CrmRuleException('La cantidad a entregar supera lo pendiente de '.$item->description.'.');
                }

                $delivery->items()->create([
                    'order_item_id' => $item->id,
                    'quantity' => $quantity,
                ]);
                $item->quantity_delivered = Money::add((string) $item->quantity_delivered, $quantity, 4);
                $item->save();
            }

            $locked->load('items');
            $pending = $locked->items->contains(
                fn ($item) => Money::gt((string) $item->quantity, (string) $item->quantity_delivered)
            );
            $next = $pending ? OrderStatus::PartiallyDelivered : OrderStatus::Completed;

            if ($locked->status !== $next) {
                $from = $locked->status;
                $locked->status = $next;
                $locked->save();
                ($this->activity)($locked, ActivityAction::StatusChanged, $user, 'Estado actualizado por la entrega.', [
                    'from' => $from->value,
                    'to' => $next->value,
                ]);
            }

            ($this->activity)($locked, ActivityAction::DeliveryRegistered, $user, 'Entrega del '.$delivery->delivered_on->toDateString().'.');

            return $delivery->load('items');
        });
    }
}
