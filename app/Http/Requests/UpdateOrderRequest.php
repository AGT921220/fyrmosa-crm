<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Features\Orders\Application\OrderRules;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order && $this->user()->can('update', $order);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return OrderRules::fields();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return OrderRules::attributes();
    }
}
