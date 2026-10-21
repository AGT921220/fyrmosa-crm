<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Features\Quotations\Application\QuotationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Quotation::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return QuotationRules::fields();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return QuotationRules::attributes();
    }
}
