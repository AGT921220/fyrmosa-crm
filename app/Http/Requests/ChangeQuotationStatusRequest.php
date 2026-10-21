<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Permission;
use App\Enums\QuotationStatus;
use App\Features\Quotations\Application\QuotationRules;
use App\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;

class ChangeQuotationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $quotation = $this->route('quotation');

        if (! $quotation instanceof Quotation) {
            return false;
        }

        $status = QuotationStatus::tryFrom((string) $this->input('status'));
        $user = $this->user();

        if ($status === QuotationStatus::Approved) {
            return $user->hasPermission(Permission::QuotationsApprove);
        }

        if ($status === QuotationStatus::Rejected) {
            return $user->hasPermission(Permission::QuotationsReject);
        }

        if ($status === QuotationStatus::Cancelled) {
            return $user->hasPermission(Permission::QuotationsCancel);
        }

        return $user->hasPermission(Permission::QuotationsUpdate);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return QuotationRules::status();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'estado',
            'comment' => 'motivo',
        ];
    }
}
