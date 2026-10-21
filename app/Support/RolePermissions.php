<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Permission;
use App\Enums\Role;

final class RolePermissions
{
    /**
     * @return list<Permission>
     */
    public static function for(Role $role): array
    {
        return match ($role) {
            Role::SuperAdmin => Permission::cases(),
            Role::Admin => array_values(array_filter(
                Permission::cases(),
                fn (Permission $permission): bool => ! $permission->managesAccess(),
            )),
            Role::Vendedor => [
                Permission::DashboardView,
                Permission::ClientsView,
                Permission::ClientsCreate,
                Permission::ClientsUpdate,
                Permission::QuotationsView,
                Permission::QuotationsCreate,
                Permission::QuotationsUpdate,
                Permission::QuotationsApprove,
                Permission::QuotationsReject,
                Permission::QuotationsCancel,
                Permission::QuotationsHistory,
                Permission::QuotationsFollowUp,
                Permission::QuotationsDuplicate,
                Permission::QuotationsVersion,
                Permission::QuotationsConvert,
                Permission::QuotationsPdf,
                Permission::QuotationsAttachments,
                Permission::OrdersView,
                Permission::OrdersCreate,
                Permission::OrdersPdf,
            ],
            Role::Facturacion => [
                Permission::DashboardView,
                Permission::ClientsView,
                Permission::QuotationsView,
                Permission::QuotationsHistory,
                Permission::QuotationsPdf,
                Permission::QuotationsAttachments,
                Permission::OrdersView,
                Permission::OrdersCreate,
                Permission::OrdersUpdate,
                Permission::OrdersConfirm,
                Permission::OrdersCancel,
                Permission::OrdersDeliver,
                Permission::OrdersFollowUp,
                Permission::OrdersPdf,
                Permission::OrdersAttachments,
            ],
        };
    }
}
