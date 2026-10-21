<?php

declare(strict_types=1);

namespace App\Features\Clients\Application;

use App\Exceptions\CrmRuleException;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

final class SaveClient
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data, ?Client $client = null): Client
    {
        return DB::transaction(function () use ($data, $client) {
            $attributes = [
                'name' => $data['name'],
                'legal_name' => $data['legal_name'] ?? null,
            ];

            if ($client === null) {
                $client = Client::query()->create($attributes);
            } else {
                $client->update($attributes);
            }

            $this->syncContacts($client, $data['contacts']);

            return $client->load('contacts');
        });
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncContacts(Client $client, array $rows): void
    {
        $kept = [];

        foreach ($rows as $row) {
            $attributes = [
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'phone' => $row['phone'],
                'email' => $row['email'],
            ];

            if (! empty($row['id'])) {
                $contact = $client->contacts()->whereKey($row['id'])->first();
                if ($contact === null) {
                    throw new CrmRuleException('El contacto no pertenece a este cliente.');
                }
                $contact->update($attributes);
                $kept[] = $contact->id;

                continue;
            }

            $kept[] = $client->contacts()->create($attributes)->id;
        }

        foreach ($client->contacts()->whereNotIn('id', $kept)->get() as $contact) {
            if ($contact->quotations()->exists() || $contact->orders()->exists()) {
                throw new CrmRuleException('No se puede quitar un contacto ligado a una cotización o un pedido.');
            }

            $contact->delete();
        }
    }
}
