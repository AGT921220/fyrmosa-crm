<?php

declare(strict_types=1);

namespace App\Features\Orders\Application;

use App\Enums\ActivityAction;
use App\Enums\OrderStatus;
use App\Exceptions\CrmRuleException;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Order;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

final class ChangeOrderStatus
{
    public function __construct(private readonly RecordActivity $activity) {}

    public function __invoke(Order $order, OrderStatus $status, User $user, ?string $comment = null): Order
    {
        if (! in_array($status, $order->status->transitions(), true)) {
            throw new CrmRuleException('No se puede cambiar de '.$order->status->label().' a '.$status->label().'.');
        }

        if ($status === OrderStatus::Cancelled && blank($comment)) {
            throw new CrmRuleException('El motivo es obligatorio para cancelar el pedido.');
        }

        if ($status === OrderStatus::Completed) {
            $order->load('items');
            $pending = $order->items->contains(
                fn ($item) => Money::gt((string) $item->quantity, (string) $item->quantity_delivered)
            );

            if ($pending) {
                throw new CrmRuleException('No se puede completar el pedido mientras existan cantidades pendientes.');
            }
        }

        return DB::transaction(function () use ($order, $status, $user, $comment) {
            $from = $order->status;
            $order->status = $status;
            $order->save();
            ($this->activity)($order, ActivityAction::StatusChanged, $user, $comment, [
                'from' => $from->value,
                'to' => $status->value,
            ]);

            return $order;
        });
    }
}
