<?php

declare(strict_types=1);

namespace App\Enums;

enum Currency: string
{
    case Mxn = 'mxn';
    case Usd = 'usd';

    public function label(): string
    {
        return match ($this) {
            self::Mxn => 'MXN',
            self::Usd => 'USD',
        };
    }
}
