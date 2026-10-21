<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Features\Orders\Application\OrderRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Order::class);
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
