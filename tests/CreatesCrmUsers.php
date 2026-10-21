<?php

declare(strict_types=1);

namespace Tests;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

trait CreatesCrmUsers
{
    protected function userWithRole(Role $role): User
    {
        $this->seed(PermissionSeeder::class);

        return User::factory()->create(['role' => $role]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function quotationPayload(User $owner, array $overrides = []): array
    {
        $client = Client::query()->create([
            'name' => 'Cliente Demo',
            'legal_name' => 'Cliente Demo SA de CV',
        ]);
        $contact = $client->contacts()->create([
            'first_name' => 'Ana',
            'last_name' => 'López',
            'phone' => '5555555555',
            'email' => 'ana@demo.test',
        ]);

        $payload = [
            'client_id' => $client->id,
            'contact_id' => $contact->id,
            'issued_on' => '2026-10-01',
            'expires_on' => '2026-10-31',
            'user_id' => $owner->id,
            'currency' => 'mxn',
            'commercial_terms' => 'Vigencia de 15 días',
            'payment_terms' => '50% de anticipo',
            'delivery_time' => '10 días',
            'internal_notes' => 'Nota interna',
            'client_notes' => 'Gracias',
            'items' => [
                [
                    'sku' => 'SKU-1',
                    'description' => 'Servicio',
                    'quantity' => '2',
                    'unit' => 'PZA',
                    'unit_price' => '100',
                    'discount_type' => 'percent',
                    'discount_value' => '10',
                    'tax_rate' => '16',
                ],
                [
                    'description' => 'Instalación',
                    'quantity' => '1',
                    'unit' => 'SERV',
                    'unit_price' => '50',
                    'discount_type' => 'amount',
                    'discount_value' => '5',
                    'tax_rate' => '0',
                ],
            ],
        ];

        return array_replace($payload, $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function orderPayload(User $owner, array $overrides = []): array
    {
        return array_replace($this->quotationPayload($owner), [
            'client_folio' => 'OC-CLIENTE-1',
            'estimated_delivery_on' => '2026-10-20',
            'notes' => 'Entregar en almacén',
            'items' => [
                [
                    'description' => 'Equipo',
                    'quantity' => '10',
                    'unit' => 'PZA',
                    'unit_price' => '25',
                    'discount_type' => 'amount',
                    'discount_value' => '0',
                    'tax_rate' => '0',
                ],
            ],
        ], $overrides);
    }
}
