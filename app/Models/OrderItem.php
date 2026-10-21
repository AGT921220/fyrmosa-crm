<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'sku',
        'description',
        'quantity',
        'quantity_delivered',
        'unit',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'subtotal',
        'tax_amount',
        'total',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'discount_type' => DiscountType::class,
            'quantity' => 'decimal:4',
            'quantity_delivered' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount_value' => 'decimal:4',
            'discount_amount' => 'decimal:2',
            'tax_rate' => 'decimal:4',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(OrderDeliveryItem::class);
    }
}
