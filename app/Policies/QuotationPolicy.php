<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::QuotationsView);
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission(Permission::QuotationsView);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::QuotationsCreate);
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission(Permission::QuotationsUpdate);
    }

    public function followUp(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission(Permission::QuotationsFollowUp);
    }

    public function viewHistory(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission(Permission::QuotationsHistory);
    }

    public function duplicate(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission(Permission::QuotationsDuplicate);
    }

    public function version(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission(Permission::QuotationsVersion);
    }

    public function convert(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission(Permission::QuotationsConvert);
    }

    public function pdf(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission(Permission::QuotationsPdf);
    }

    public function attach(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission(Permission::QuotationsAttachments);
    }

    public function changeStatus(User $user, Quotation $quotation): bool
    {
        return $user->hasPermission(Permission::QuotationsUpdate)
            || $user->hasPermission(Permission::QuotationsApprove)
            || $user->hasPermission(Permission::QuotationsReject)
            || $user->hasPermission(Permission::QuotationsCancel);
    }
}
