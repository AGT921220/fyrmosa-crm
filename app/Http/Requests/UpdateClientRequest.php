<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client instanceof Client && $this->user()->can('update', $client);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return (new StoreClientRequest)->rules();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return (new StoreClientRequest)->attributes();
    }
}
