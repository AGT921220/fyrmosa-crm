<?php

declare(strict_types=1);

namespace App\Features\Quotations\Application;

use App\Enums\ActivityAction;
use App\Enums\QuotationStatus;
use App\Exceptions\CrmRuleException;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ChangeQuotationStatus
{
    public function __construct(
        private readonly RecordActivity $activity,
        private readonly SnapshotQuotation $snapshot,
    ) {}

    public function __invoke(Quotation $quotation, QuotationStatus $status, User $user, ?string $comment = null): Quotation
    {
        if (! in_array($status, $quotation->status->transitions(), true)) {
            throw new CrmRuleException('No se puede cambiar de '.$quotation->status->label().' a '.$status->label().'.');
        }

        if ($status->requiresComment() && blank($comment)) {
            $message = $status === QuotationStatus::Lost
                ? 'La nota del motivo es obligatoria cuando la cotización se pierde.'
                : 'El motivo es obligatorio para rechazar.';

            throw new CrmRuleException($message);
        }

        return DB::transaction(function () use ($quotation, $status, $user, $comment) {
            $from = $quotation->status;
            $quotation->status = $status;
            $quotation->save();

            ($this->activity)($quotation, ActivityAction::StatusChanged, $user, $comment, [
                'from' => $from->value,
                'to' => $status->value,
            ]);

            if ($status === QuotationStatus::Won) {
                ($this->snapshot)($quotation, $user);
            }

            return $quotation;
        });
    }
}
