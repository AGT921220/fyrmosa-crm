<?php

declare(strict_types=1);

namespace App\Features\Quotations\Application;

use App\Enums\ActivityAction;
use App\Enums\FollowUpType;
use App\Exceptions\CrmRuleException;
use App\Features\Shared\Application\RecordActivity;
use App\Models\FollowUp;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class AddFollowUp
{
    public function __construct(private readonly RecordActivity $activity) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Model $document, User $actor, array $data): FollowUp
    {
        $this->guard($document);

        return DB::transaction(function () use ($document, $actor, $data) {
            $followUp = $document->followUps()->create([
                'type' => $data['type'],
                'result' => $data['result'] ?? null,
                'notes' => $data['notes'] ?? null,
                'occurred_at' => $data['occurred_at'],
                'next_follow_up_at' => $data['next_follow_up_at'] ?? null,
                'user_id' => $data['user_id'],
            ]);

            if (array_key_exists('next_follow_up_at', $data)) {
                $document->next_follow_up_at = $data['next_follow_up_at'] ?: null;
                $document->save();
            }

            $type = $followUp->type instanceof FollowUpType ? $followUp->type->label() : (string) $followUp->type;
            ($this->activity)($document, ActivityAction::FollowUp, $actor, $type.($followUp->result ? ': '.$followUp->result : ''));

            return $followUp;
        });
    }

    private function guard(Model $document): void
    {
        if ($document instanceof Quotation && ! $document->status->acceptsFollowUps()) {
            throw new CrmRuleException('No se pueden registrar seguimientos en una cotización '.$document->status->label().'.');
        }

        if ($document instanceof Order && ! $document->status->acceptsFollowUps()) {
            throw new CrmRuleException('No se pueden registrar seguimientos en un pedido '.$document->status->label().'.');
        }
    }
}
