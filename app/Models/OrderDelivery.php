<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderDelivery extends Model
{
    protected $fillable = [
        'order_id',
        'delivered_on',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'delivered_on' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderDeliveryItem::class);
    }
}
