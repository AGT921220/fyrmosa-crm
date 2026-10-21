<?php

declare(strict_types=1);

namespace App\Enums;

enum FollowUpType: string
{
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case Whatsapp = 'whatsapp';
    case Note = 'note';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Llamada',
            self::Email => 'Correo',
            self::Meeting => 'Reunión',
            self::Whatsapp => 'WhatsApp',
            self::Note => 'Nota interna',
        };
    }
}
