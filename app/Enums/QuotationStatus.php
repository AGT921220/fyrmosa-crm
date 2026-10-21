<?php

declare(strict_types=1);

namespace App\Enums;

enum QuotationStatus: string
{
    case Requested = 'solicitada';
    case Quoted = 'cotizada';
    case Negotiating = 'en_negociacion';
    case Rejected = 'rechazada';
    case Won = 'ganada';
    case Lost = 'perdida';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Solicitada',
            self::Quoted => 'Cotizada',
            self::Negotiating => 'En negociación',
            self::Rejected => 'Rechazada',
            self::Won => 'Ganada',
            self::Lost => 'Perdida',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Requested => 'bg-secondary-lt',
            self::Quoted => 'bg-azure-lt',
            self::Negotiating => 'bg-yellow-lt',
            self::Rejected => 'bg-red-lt',
            self::Won => 'bg-green-lt',
            self::Lost => 'bg-dark-lt',
        };
    }

    public function isEditable(): bool
    {
        return match ($this) {
            self::Requested, self::Quoted, self::Negotiating => true,
            self::Rejected, self::Won, self::Lost => false,
        };
    }

    public function canVersion(): bool
    {
        return match ($this) {
            self::Quoted, self::Negotiating, self::Won => true,
            self::Requested, self::Rejected, self::Lost => false,
        };
    }

    public function acceptsFollowUps(): bool
    {
        return match ($this) {
            self::Rejected, self::Lost => false,
            default => true,
        };
    }

    public function requiresComment(): bool
    {
        return match ($this) {
            self::Rejected, self::Lost => true,
            default => false,
        };
    }

    /**
     * @return list<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Requested => [self::Quoted, self::Rejected, self::Lost],
            self::Quoted => [self::Negotiating, self::Rejected, self::Won, self::Lost],
            self::Negotiating => [self::Quoted, self::Rejected, self::Won, self::Lost],
            self::Rejected, self::Won, self::Lost => [],
        };
    }
}
