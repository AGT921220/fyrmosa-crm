<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Enums\QuotationStatus;
use App\Features\Dashboard\Application\CrmMetrics;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CrmMetrics $metrics): View
    {
        abort_unless($request->user()?->hasPermission(Permission::DashboardView), 403);

        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'client_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'quotation_status' => ['nullable', Rule::enum(QuotationStatus::class)],
            'order_status' => ['nullable', Rule::enum(OrderStatus::class)],
        ]);

        return view('dashboard.index', [
            'filters' => $filters,
            'metrics' => $metrics($filters),
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'quotationStatuses' => QuotationStatus::cases(),
            'orderStatuses' => OrderStatus::cases(),
        ]);
    }
}
