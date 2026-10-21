<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'action',
        'user_id',
        'comment',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'action' => ActivityAction::class,
            'properties' => 'array',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
