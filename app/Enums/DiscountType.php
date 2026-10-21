<?php

declare(strict_types=1);

namespace App\Enums;

enum DiscountType: string
{
    case Percent = 'percent';
    case Amount = 'amount';

    public function label(): string
    {
        return match ($this) {
            self::Percent => 'Porcentaje',
            self::Amount => 'Importe',
        };
    }
}
