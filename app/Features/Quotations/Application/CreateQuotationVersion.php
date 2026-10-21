<?php

declare(strict_types=1);

namespace App\Features\Quotations\Application;

use App\Enums\ActivityAction;
use App\Enums\QuotationStatus;
use App\Exceptions\CrmRuleException;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Quotation;
use App\Models\User;
use App\Support\Folio;
use Illuminate\Support\Facades\DB;

final class CreateQuotationVersion
{
    public function __construct(
        private readonly RecordActivity $activity,
        private readonly SnapshotQuotation $snapshot,
    ) {}

    public function __invoke(Quotation $source, User $user): Quotation
    {
        if (! $source->status->canVersion()) {
            throw new CrmRuleException('Solo se puede versionar una cotización enviada, en seguimiento, en negociación, aprobada o vencida.');
        }

        return DB::transaction(function () use ($source, $user) {
            $rootId = $source->root_id ?? $source->id;
            $chain = Quotation::query()
                ->where(function ($query) use ($rootId) {
                    $query->where('id', $rootId)->orWhere('root_id', $rootId);
                })
                ->lockForUpdate()
                ->get();

            if ($chain->contains(fn (Quotation $quotation) => $quotation->status === QuotationStatus::Draft)) {
                throw new CrmRuleException('Ya existe un borrador en esta cadena de versiones.');
            }

            $source->load('items');
            ($this->snapshot)($source, $user);
            $version = ((int) $chain->max('version_number')) + 1;

            $copy = Quotation::query()->create([
                'folio' => Folio::format('COT', $source->sequence_number, $version),
                'sequence_number' => $source->sequence_number,
                'version_number' => $version,
                'parent_id' => $source->id,
                'root_id' => $rootId,
                'client_id' => $source->client_id,
                'contact_id' => $source->contact_id,
                'client_name' => $source->client_name,
                'legal_name' => $source->legal_name,
                'contact_first_name' => $source->contact_first_name,
                'contact_last_name' => $source->contact_last_name,
                'email' => $source->email,
                'phone' => $source->phone,
                'issued_on' => now()->toDateString(),
                'expires_on' => now()->addDays(15)->toDateString(),
                'user_id' => $source->user_id,
                'currency' => $source->currency,
                'commercial_terms' => $source->commercial_terms,
                'payment_terms' => $source->payment_terms,
                'delivery_time' => $source->delivery_time,
                'internal_notes' => $source->internal_notes,
                'client_notes' => $source->client_notes,
                'status' => QuotationStatus::Draft,
                'subtotal' => $source->subtotal,
                'discount_total' => $source->discount_total,
                'tax_total' => $source->tax_total,
                'total' => $source->total,
            ]);

            $copy->items()->createMany($source->items->map(fn ($item) => [
                'sku' => $item->sku,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'discount_type' => $item->discount_type,
                'discount_value' => $item->discount_value,
                'discount_amount' => $item->discount_amount,
                'tax_rate' => $item->tax_rate,
                'subtotal' => $item->subtotal,
                'tax_amount' => $item->tax_amount,
                'total' => $item->total,
                'position' => $item->position,
            ])->all());

            ($this->activity)($source, ActivityAction::VersionCreated, $user, 'Se creó la versión '.$version.' ('.$copy->folio.').', [
                'version_id' => $copy->id,
            ]);
            ($this->activity)($copy, ActivityAction::Created, $user, 'Nueva versión de '.$source->folio.'.');

            return $copy;
        });
    }
}
