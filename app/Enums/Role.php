<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'superadmin';
    case Admin = 'admin';
    case Vendedor = 'vendedor';
    case Facturacion = 'facturacion';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Superadmin',
            self::Admin => 'Admin',
            self::Vendedor => 'Vendedor',
            self::Facturacion => 'Facturación',
        };
    }

    public static function isBuiltIn(string $name): bool
    {
        return self::tryFrom($name) !== null;
    }
}
