<?php

declare(strict_types=1);

namespace App\Features\Quotations\Application;

use App\Enums\ActivityAction;
use App\Exceptions\CrmRuleException;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteQuotation
{
    public function __construct(
        private readonly RecordActivity $activity,
    ) {}

    public function __invoke(Quotation $quotation, User $user, string $reason): void
    {
        if (blank($reason)) {
            throw new CrmRuleException('El motivo de eliminación es obligatorio.');
        }

        DB::transaction(function () use ($quotation, $user, $reason): void {
            ($this->activity)($quotation, ActivityAction::Deleted, $user, $reason);

            $quotation->deleted_reason = $reason;
            $quotation->deleted_by = $user->id;
            $quotation->save();
            $quotation->delete();
        });
    }
}
