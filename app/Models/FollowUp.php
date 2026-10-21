<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FollowUpType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FollowUp extends Model
{
    protected $fillable = [
        'followable_type',
        'followable_id',
        'type',
        'result',
        'notes',
        'occurred_at',
        'next_follow_up_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => FollowUpType::class,
            'occurred_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
        ];
    }

    public function followable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
