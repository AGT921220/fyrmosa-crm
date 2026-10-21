<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Features\Quotations\Application\QuotationRules;
use App\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;

class DeleteQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $quotation = $this->route('quotation');

        return $quotation instanceof Quotation && $this->user()->can('delete', $quotation);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return QuotationRules::delete();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'reason' => 'motivo',
        ];
    }
}
