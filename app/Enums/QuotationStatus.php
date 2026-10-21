<?php

declare(strict_types=1);

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'borrador';
    case Sent = 'enviada';
    case Following = 'en_seguimiento';
    case Negotiating = 'en_negociacion';
    case Approved = 'aprobada';
    case Rejected = 'rechazada';
    case Expired = 'vencida';
    case Cancelled = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Sent => 'Enviada',
            self::Following => 'En seguimiento',
            self::Negotiating => 'En negociación',
            self::Approved => 'Aprobada',
            self::Rejected => 'Rechazada',
            self::Expired => 'Vencida',
            self::Cancelled => 'Cancelada',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'bg-secondary-lt',
            self::Sent => 'bg-azure-lt',
            self::Following => 'bg-blue-lt',
            self::Negotiating => 'bg-yellow-lt',
            self::Approved => 'bg-green-lt',
            self::Rejected => 'bg-red-lt',
            self::Expired => 'bg-orange-lt',
            self::Cancelled => 'bg-dark-lt',
        };
    }

    public function isEditable(): bool
    {
        return match ($this) {
            self::Draft, self::Sent, self::Following, self::Negotiating => true,
            self::Approved, self::Rejected, self::Expired, self::Cancelled => false,
        };
    }

    public function canVersion(): bool
    {
        return match ($this) {
            self::Sent, self::Following, self::Negotiating, self::Approved, self::Expired => true,
            self::Draft, self::Rejected, self::Cancelled => false,
        };
    }

    public function acceptsFollowUps(): bool
    {
        return match ($this) {
            self::Rejected, self::Cancelled => false,
            default => true,
        };
    }

    /**
     * @return list<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Draft => [self::Sent, self::Cancelled],
            self::Sent => [self::Following, self::Negotiating, self::Approved, self::Rejected, self::Expired, self::Cancelled],
            self::Following => [self::Sent, self::Negotiating, self::Approved, self::Rejected, self::Expired, self::Cancelled],
            self::Negotiating => [self::Following, self::Approved, self::Rejected, self::Expired, self::Cancelled],
            self::Approved, self::Rejected, self::Expired, self::Cancelled => [],
        };
    }
}
