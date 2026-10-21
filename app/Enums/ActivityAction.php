<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case PricesChanged = 'prices_changed';
    case TermsChanged = 'terms_changed';
    case StatusChanged = 'status_changed';
    case FollowUp = 'follow_up';
    case VersionCreated = 'version_created';
    case Duplicated = 'duplicated';
    case Converted = 'converted';
    case AttachmentAdded = 'attachment_added';
    case AttachmentRemoved = 'attachment_removed';
    case DeliveryRegistered = 'delivery_registered';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Creación',
            self::Updated => 'Modificación',
            self::PricesChanged => 'Cambio de precios',
            self::TermsChanged => 'Cambio de condiciones',
            self::StatusChanged => 'Cambio de estado',
            self::FollowUp => 'Seguimiento',
            self::VersionCreated => 'Nueva versión',
            self::Duplicated => 'Duplicado',
            self::Converted => 'Conversión a pedido',
            self::AttachmentAdded => 'Documento adjunto',
            self::AttachmentRemoved => 'Documento eliminado',
            self::DeliveryRegistered => 'Entrega registrada',
        };
    }
}
