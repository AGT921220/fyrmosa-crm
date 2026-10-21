<?php

declare(strict_types=1);

namespace App\Features\Shared\Application;

use App\Exceptions\CrmRuleException;
use App\Models\Contact;

final class PartyAttributes
{
    /**
     * @return array<string, mixed>
     */
    public static function capture(int $clientId, int $contactId): array
    {
        $contact = Contact::query()->with('client')->find($contactId);

        if ($contact === null || $contact->client === null || (int) $contact->client_id !== $clientId) {
            throw new CrmRuleException('El contacto no pertenece al cliente seleccionado.');
        }

        return [
            'client_id' => $contact->client_id,
            'contact_id' => $contact->id,
            'client_name' => $contact->client->name,
            'legal_name' => $contact->client->legal_name,
            'contact_first_name' => $contact->first_name,
            'contact_last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
        ];
    }
}
