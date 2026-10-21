<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'borrador';
    case PendingConfirmation = 'pendiente_de_confirmacion';
    case Confirmed = 'confirmada';
    case InProgress = 'en_proceso';
    case PartiallyDelivered = 'parcialmente_entregada';
    case Completed = 'completada';
    case Cancelled = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::PendingConfirmation => 'Pendiente de confirmación',
            self::Confirmed => 'Confirmada',
            self::InProgress => 'En proceso',
            self::PartiallyDelivered => 'Parcialmente entregada',
            self::Completed => 'Completada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'bg-secondary-lt',
            self::PendingConfirmation => 'bg-azure-lt',
            self::Confirmed => 'bg-blue-lt',
            self::InProgress => 'bg-yellow-lt',
            self::PartiallyDelivered => 'bg-orange-lt',
            self::Completed => 'bg-green-lt',
            self::Cancelled => 'bg-red-lt',
        };
    }

    public function isEditable(): bool
    {
        return match ($this) {
            self::Draft, self::PendingConfirmation => true,
            default => false,
        };
    }

    public function acceptsDeliveries(): bool
    {
        return match ($this) {
            self::Confirmed, self::InProgress, self::PartiallyDelivered => true,
            default => false,
        };
    }

    public function isOpen(): bool
    {
        return match ($this) {
            self::Completed, self::Cancelled => false,
            default => true,
        };
    }

    public function acceptsFollowUps(): bool
    {
        return $this !== self::Cancelled;
    }

    /**
     * @return list<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Draft => [self::PendingConfirmation, self::Cancelled],
            self::PendingConfirmation => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::InProgress, self::PartiallyDelivered, self::Completed, self::Cancelled],
            self::InProgress => [self::PartiallyDelivered, self::Completed, self::Cancelled],
            self::PartiallyDelivered => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }
}
