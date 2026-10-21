<?php

declare(strict_types=1);

namespace App\Features\Orders\Application;

use App\Enums\ActivityAction;
use App\Exceptions\CrmRuleException;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteOrder
{
    public function __construct(
        private readonly RecordActivity $activity,
    ) {}

    public function __invoke(Order $order, User $user, string $reason): void
    {
        if (blank($reason)) {
            throw new CrmRuleException('El motivo de eliminación es obligatorio.');
        }

        DB::transaction(function () use ($order, $user, $reason): void {
            ($this->activity)($order, ActivityAction::Deleted, $user, $reason);

            $order->deleted_reason = $reason;
            $order->deleted_by = $user->id;
            $order->save();
            $order->delete();
        });
    }
}
