<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Permission;
use App\Enums\OrderStatus;
use App\Features\Orders\Application\OrderRules;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class ChangeOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        if (! $order instanceof Order) {
            return false;
        }

        $status = OrderStatus::tryFrom((string) $this->input('status'));
        $user = $this->user();

        if ($status === OrderStatus::Cancelled) {
            return $user->hasPermission(Permission::OrdersCancel);
        }

        if (in_array($status, [
            OrderStatus::Confirmed,
            OrderStatus::InProgress,
            OrderStatus::PartiallyDelivered,
            OrderStatus::Completed,
        ], true)) {
            return $user->hasPermission(Permission::OrdersConfirm);
        }

        return $user->hasPermission(Permission::OrdersUpdate);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return OrderRules::status();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'estado',
            'comment' => 'motivo',
        ];
    }
}
