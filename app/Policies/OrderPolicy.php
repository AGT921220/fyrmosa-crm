<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::OrdersView);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermission(Permission::OrdersView);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::OrdersCreate);
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasPermission(Permission::OrdersUpdate);
    }

    public function deliver(User $user, Order $order): bool
    {
        return $user->hasPermission(Permission::OrdersDeliver);
    }

    public function followUp(User $user, Order $order): bool
    {
        return $user->hasPermission(Permission::OrdersFollowUp);
    }

    public function pdf(User $user, Order $order): bool
    {
        return $user->hasPermission(Permission::OrdersPdf);
    }

    public function attach(User $user, Order $order): bool
    {
        return $user->hasPermission(Permission::OrdersAttachments);
    }

    public function changeStatus(User $user, Order $order): bool
    {
        return $user->hasPermission(Permission::OrdersUpdate)
            || $user->hasPermission(Permission::OrdersConfirm)
            || $user->hasPermission(Permission::OrdersCancel);
    }
}
