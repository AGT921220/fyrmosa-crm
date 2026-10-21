<?php

declare(strict_types=1);

namespace App\Features\Orders\Application;

use App\Enums\Currency;
use App\Enums\DiscountType;
use App\Enums\OrderStatus;
use Illuminate\Validation\Rule;

final class OrderRules
{
    /**
     * @return array<string, mixed>
     */
    public static function fields(): array
    {
        return [
            'client_folio' => ['nullable', 'string', 'max:80'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'contact_id' => ['required', 'integer', Rule::exists('contacts', 'id')->where(fn ($query) => $query->where('client_id', request()->input('client_id')))],
            'issued_on' => ['required', 'date'],
            'estimated_delivery_on' => ['nullable', 'date', 'after_or_equal:issued_on'],
            'user_id' => ['required', 'exists:users,id'],
            'currency' => ['required', Rule::enum(Currency::class)],
            'commercial_terms' => ['nullable', 'string', 'max:5000'],
            'payment_terms' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['nullable', 'string', 'max:80'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['required', 'string', 'max:40'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
            'items.*.discount_type' => ['required', Rule::enum(DiscountType::class)],
            'items.*.discount_value' => ['nullable', 'numeric', 'gte:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'client_folio' => 'folio del cliente',
            'client_id' => 'cliente',
            'contact_id' => 'contacto',
            'issued_on' => 'fecha de emisión',
            'estimated_delivery_on' => 'fecha estimada de entrega',
            'user_id' => 'responsable',
            'currency' => 'moneda',
            'commercial_terms' => 'condiciones comerciales',
            'payment_terms' => 'condiciones de pago',
            'notes' => 'observaciones',
            'items' => 'conceptos',
            'items.*.description' => 'descripción',
            'items.*.quantity' => 'cantidad',
            'items.*.unit' => 'unidad',
            'items.*.unit_price' => 'precio unitario',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function status(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'comment' => [
                'nullable',
                'string',
                'max:2000',
                Rule::requiredIf(fn () => request()->input('status') === OrderStatus::Cancelled->value),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function delivery(): array
    {
        return [
            'delivered_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['nullable', 'numeric', 'gte:0'],
        ];
    }
}
