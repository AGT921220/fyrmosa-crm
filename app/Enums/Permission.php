<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case DashboardView = 'dashboard.view';

    case ClientsView = 'clients.view';
    case ClientsCreate = 'clients.create';
    case ClientsUpdate = 'clients.update';
    case ClientsDelete = 'clients.delete';

    case QuotationsView = 'quotations.view';
    case QuotationsCreate = 'quotations.create';
    case QuotationsUpdate = 'quotations.update';
    case QuotationsApprove = 'quotations.approve';
    case QuotationsReject = 'quotations.reject';
    case QuotationsCancel = 'quotations.cancel';
    case QuotationsHistory = 'quotations.history';
    case QuotationsFollowUp = 'quotations.follow_up';
    case QuotationsDuplicate = 'quotations.duplicate';
    case QuotationsVersion = 'quotations.version';
    case QuotationsConvert = 'quotations.convert';
    case QuotationsPdf = 'quotations.pdf';
    case QuotationsAttachments = 'quotations.attachments';

    case OrdersView = 'orders.view';
    case OrdersCreate = 'orders.create';
    case OrdersUpdate = 'orders.update';
    case OrdersConfirm = 'orders.confirm';
    case OrdersCancel = 'orders.cancel';
    case OrdersDeliver = 'orders.deliver';
    case OrdersFollowUp = 'orders.follow_up';
    case OrdersPdf = 'orders.pdf';
    case OrdersAttachments = 'orders.attachments';

    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';

    case RolesView = 'roles.view';
    case RolesCreate = 'roles.create';
    case RolesUpdate = 'roles.update';
    case RolesDelete = 'roles.delete';

    public function label(): string
    {
        return match ($this) {
            self::DashboardView => 'Ver dashboard',
            self::ClientsView => 'Ver clientes',
            self::ClientsCreate => 'Crear clientes',
            self::ClientsUpdate => 'Editar clientes',
            self::ClientsDelete => 'Eliminar clientes',
            self::QuotationsView => 'Ver cotizaciones',
            self::QuotationsCreate => 'Crear cotizaciones',
            self::QuotationsUpdate => 'Editar cotizaciones',
            self::QuotationsApprove => 'Aprobar cotizaciones',
            self::QuotationsReject => 'Rechazar cotizaciones',
            self::QuotationsCancel => 'Cancelar cotizaciones',
            self::QuotationsHistory => 'Ver histórico de cotizaciones',
            self::QuotationsFollowUp => 'Registrar seguimientos de cotizaciones',
            self::QuotationsDuplicate => 'Duplicar cotizaciones',
            self::QuotationsVersion => 'Crear versiones',
            self::QuotationsConvert => 'Convertir a pedido',
            self::QuotationsPdf => 'Ver PDF de cotizaciones',
            self::QuotationsAttachments => 'Adjuntar documentos en cotizaciones',
            self::OrdersView => 'Ver pedidos',
            self::OrdersCreate => 'Crear pedidos',
            self::OrdersUpdate => 'Editar pedidos',
            self::OrdersConfirm => 'Confirmar y avanzar pedidos',
            self::OrdersCancel => 'Cancelar pedidos',
            self::OrdersDeliver => 'Registrar entregas',
            self::OrdersFollowUp => 'Registrar seguimientos de pedidos',
            self::OrdersPdf => 'Ver PDF de pedidos',
            self::OrdersAttachments => 'Adjuntar documentos en pedidos',
            self::UsersView => 'Ver usuarios',
            self::UsersCreate => 'Crear usuarios',
            self::UsersUpdate => 'Editar usuarios',
            self::UsersDelete => 'Eliminar usuarios',
            self::RolesView => 'Ver roles',
            self::RolesCreate => 'Crear roles',
            self::RolesUpdate => 'Editar roles',
            self::RolesDelete => 'Eliminar roles',
        };
    }

    public function group(): string
    {
        return match (true) {
            str_starts_with($this->value, 'dashboard.') => 'Dashboard',
            str_starts_with($this->value, 'clients.') => 'Clientes',
            str_starts_with($this->value, 'quotations.') => 'Cotizaciones',
            str_starts_with($this->value, 'orders.') => 'Pedidos',
            str_starts_with($this->value, 'users.') => 'Usuarios',
            default => 'Roles',
        };
    }

    public function managesAccess(): bool
    {
        return str_starts_with($this->value, 'users.') || str_starts_with($this->value, 'roles.');
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
