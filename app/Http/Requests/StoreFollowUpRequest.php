<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Features\Quotations\Application\QuotationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        $quotation = $this->route('quotation');

        if ($quotation) {
            return $this->user()->can('followUp', $quotation);
        }

        $order = $this->route('order');

        return $order && $this->user()->can('followUp', $order);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return QuotationRules::followUp();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'tipo',
            'occurred_at' => 'fecha del contacto',
            'result' => 'resultado',
            'notes' => 'notas',
            'next_follow_up_at' => 'próximo seguimiento',
            'user_id' => 'responsable',
        ];
    }
}
