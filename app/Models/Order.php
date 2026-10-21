<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasCommercialRecords;
    use SoftDeletes;

    public const SORTABLE = [
        'folio',
        'client_name',
        'issued_on',
        'estimated_delivery_on',
        'total',
        'status',
    ];

    protected $fillable = [
        'folio',
        'sequence_number',
        'quotation_id',
        'quotation_version',
        'client_folio',
        'client_id',
        'contact_id',
        'client_name',
        'legal_name',
        'contact_first_name',
        'contact_last_name',
        'email',
        'phone',
        'issued_on',
        'estimated_delivery_on',
        'user_id',
        'converted_by',
        'converted_at',
        'currency',
        'commercial_terms',
        'payment_terms',
        'notes',
        'status',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'next_follow_up_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'estimated_delivery_on' => 'date',
            'converted_at' => 'datetime',
            'currency' => Currency::class,
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'next_follow_up_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function contactName(): string
    {
        return trim($this->contact_first_name.' '.$this->contact_last_name);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function converter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('position');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(OrderDelivery::class)->orderBy('delivered_on');
    }

    public function scopeFiltered(Builder $query, array $filters): void
    {
        $query->when($filters['q'] ?? null, function (Builder $query, string $term) {
            $like = '%'.$term.'%';
            $query->where(function (Builder $query) use ($like) {
                $query->where('folio', 'like', $like)
                    ->orWhere('client_folio', 'like', $like)
                    ->orWhere('client_name', 'like', $like)
                    ->orWhere('legal_name', 'like', $like)
                    ->orWhere('contact_first_name', 'like', $like)
                    ->orWhere('contact_last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhereHas('quotation', fn (Builder $query) => $query->where('folio', 'like', $like));
            });
        });

        $query->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
        $query->when($filters['currency'] ?? null, fn (Builder $query, string $currency) => $query->where('currency', $currency));
        $query->when($filters['client_id'] ?? null, fn (Builder $query, string $clientId) => $query->where('client_id', $clientId));
        $query->when($filters['user_id'] ?? null, fn (Builder $query, string $userId) => $query->where('user_id', $userId));
        $query->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('issued_on', '>=', $date));
        $query->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('issued_on', '<=', $date));

        if (($filters['follow_up'] ?? null) === 'overdue') {
            $query->whereNotNull('next_follow_up_at')->where('next_follow_up_at', '<', now());
        }

        if (($filters['follow_up'] ?? null) === 'pending') {
            $query->whereNotNull('next_follow_up_at')->where('next_follow_up_at', '>=', now());
        }

        $sort = in_array($filters['sort'] ?? '', self::SORTABLE, true) ? $filters['sort'] : 'issued_on';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction);
    }
}
