<?php

declare(strict_types=1);

namespace App\Features\Quotations\Application;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

final class ExpireQuotations
{
    public function __invoke(): int
    {
        $ids = Quotation::query()
            ->whereIn('status', [
                QuotationStatus::Sent->value,
                QuotationStatus::Following->value,
                QuotationStatus::Negotiating->value,
            ])
            ->whereDate('expires_on', '<', now()->toDateString())
            ->orderBy('id')
            ->pluck('id');

        $count = 0;

        foreach ($ids as $id) {
            $expired = DB::transaction(function () use ($id) {
                $quotation = Quotation::query()->whereKey($id)->lockForUpdate()->first();

                if ($quotation === null || ! in_array(QuotationStatus::Expired, $quotation->status->transitions(), true)) {
                    return false;
                }

                $from = $quotation->status;
                $quotation->status = QuotationStatus::Expired;
                $quotation->save();
                $quotation->activities()->create([
                    'action' => 'status_changed',
                    'user_id' => null,
                    'comment' => 'Vencida por fecha de vencimiento.',
                    'properties' => [
                        'from' => $from->value,
                        'to' => QuotationStatus::Expired->value,
                    ],
                ]);

                return true;
            });

            if ($expired) {
                $count++;
            }
        }

        return $count;
    }
}
