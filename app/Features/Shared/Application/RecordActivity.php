<?php

declare(strict_types=1);

namespace App\Features\Shared\Application;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class RecordActivity
{
    public function __invoke(Model $subject, ActivityAction $action, ?User $user, ?string $comment = null, array $properties = []): ActivityLog
    {
        return $subject->activities()->create([
            'action' => $action,
            'user_id' => $user?->id,
            'comment' => $comment,
            'properties' => $properties === [] ? null : $properties,
        ]);
    }
}
