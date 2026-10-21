<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Client::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'contacts' => ['required', 'array', 'min:1'],
            'contacts.*.id' => ['nullable', 'integer'],
            'contacts.*.first_name' => ['required', 'string', 'max:255'],
            'contacts.*.last_name' => ['required', 'string', 'max:255'],
            'contacts.*.phone' => ['required', 'string', 'max:40'],
            'contacts.*.email' => ['required', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'legal_name' => 'razón social',
            'contacts' => 'contactos',
            'contacts.*.first_name' => 'nombre del contacto',
            'contacts.*.last_name' => 'apellido del contacto',
            'contacts.*.phone' => 'teléfono del contacto',
            'contacts.*.email' => 'correo del contacto',
        ];
    }
}
