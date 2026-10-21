<?php

declare(strict_types=1);

namespace App\Features\Dashboard\Application;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\QuotationStatus;
use App\Models\Order;
use App\Models\Quotation;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class CrmMetrics
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function __invoke(array $filters): array
    {
        $quotationRows = $this->quotations($filters)
            ->toBase()
            ->selectRaw('currency, status, COUNT(*) as aggregate, SUM(total) as amount')
            ->groupBy('currency', 'status')
            ->get();

        $orderRows = $this->orders($filters)
            ->toBase()
            ->selectRaw('currency, status, COUNT(*) as aggregate, SUM(total) as amount')
            ->groupBy('currency', 'status')
            ->get();

        $approved = $this->quotations($filters)
            ->where('status', QuotationStatus::Approved->value)
            ->toBase()
            ->selectRaw('currency, COUNT(*) as aggregate')
            ->groupBy('currency')
            ->pluck('aggregate', 'currency');

        $converted = $this->quotations($filters)
            ->where('status', QuotationStatus::Approved->value)
            ->whereHas('order')
            ->toBase()
            ->selectRaw('currency, COUNT(*) as aggregate')
            ->groupBy('currency')
            ->pluck('aggregate', 'currency');

        $month = $this->monthExpression();
        $monthRows = $this->quotations($filters)
            ->toBase()
            ->selectRaw("currency, {$month} as month, COUNT(*) as aggregate, SUM(total) as quoted, SUM(CASE WHEN status = ? THEN total ELSE 0 END) as approved", [
                QuotationStatus::Approved->value,
            ])
            ->groupBy('currency', DB::raw($month))
            ->orderBy('month')
            ->get();

        $currencies = [];

        foreach (Currency::cases() as $currency) {
            $quotes = $quotationRows->where('currency', $currency->value);
            $orders = $orderRows->where('currency', $currency->value);
            $approvedCount = (int) ($approved[$currency->value] ?? 0);
            $convertedCount = (int) ($converted[$currency->value] ?? 0);

            $currencies[$currency->value] = [
                'label' => $currency->label(),
                'quotations_created' => (int) $quotes->sum('aggregate'),
                'quotations_pending' => $this->countStatuses($quotes, [
                    QuotationStatus::Sent,
                    QuotationStatus::Following,
                    QuotationStatus::Negotiating,
                ]),
                'quotations_approved' => $this->countStatuses($quotes, [QuotationStatus::Approved]),
                'quotations_rejected' => $this->countStatuses($quotes, [QuotationStatus::Rejected]),
                'quotations_expired' => $this->countStatuses($quotes, [QuotationStatus::Expired]),
                'quoted_amount' => $this->sumAmount($quotes),
                'approved_amount' => $this->sumAmount($quotes->where('status', QuotationStatus::Approved->value)),
                'orders_active' => $this->countStatuses($orders, [
                    OrderStatus::PendingConfirmation,
                    OrderStatus::Confirmed,
                    OrderStatus::InProgress,
                    OrderStatus::PartiallyDelivered,
                ]),
                'orders_completed' => $this->countStatuses($orders, [OrderStatus::Completed]),
                'orders_amount' => $this->sumAmount($orders->where('status', '!=', OrderStatus::Cancelled->value)),
                'status_counts' => $quotes->mapWithKeys(fn ($row) => [$row->status => (int) $row->aggregate])->all(),
                'conversion' => [
                    'approved' => $approvedCount,
                    'converted' => $convertedCount,
                    'rate' => $approvedCount === 0 ? 0 : round(($convertedCount / $approvedCount) * 100, 1),
                ],
            ];
        }

        $months = $monthRows->pluck('month')->unique()->sort()->values()->all();

        return [
            'currencies' => $currencies,
            'months' => $months,
            'series' => $this->series($monthRows, $months),
            'follow_ups' => [
                'pending' => $this->followUps($filters, false),
                'overdue' => $this->followUps($filters, true),
            ],
        ];
    }

    private function quotations(array $filters): Builder
    {
        return Quotation::query()
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('issued_on', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('issued_on', '<=', $date))
            ->when($filters['client_id'] ?? null, fn (Builder $query, string $id) => $query->where('client_id', $id))
            ->when($filters['user_id'] ?? null, fn (Builder $query, string $id) => $query->where('user_id', $id))
            ->when($filters['quotation_status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }

    private function orders(array $filters): Builder
    {
        return Order::query()
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('issued_on', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('issued_on', '<=', $date))
            ->when($filters['client_id'] ?? null, fn (Builder $query, string $id) => $query->where('client_id', $id))
            ->when($filters['user_id'] ?? null, fn (Builder $query, string $id) => $query->where('user_id', $id))
            ->when($filters['order_status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }

    /**
     * @param  iterable<int, object>  $rows
     * @param  list<QuotationStatus|OrderStatus>  $statuses
     */
    private function countStatuses(iterable $rows, array $statuses): int
    {
        $values = array_map(fn ($status) => $status->value, $statuses);
        $total = 0;

        foreach ($rows as $row) {
            if (in_array($row->status, $values, true)) {
                $total += (int) $row->aggregate;
            }
        }

        return $total;
    }

    /**
     * @param  iterable<int, object>  $rows
     */
    private function sumAmount(iterable $rows): string
    {
        $amount = Money::zero();

        foreach ($rows as $row) {
            $amount = Money::add($amount, Money::of($row->amount ?? 0));
        }

        return $amount;
    }

    private function monthExpression(): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "strftime('%Y-%m', issued_on)";
        }

        return "DATE_FORMAT(issued_on, '%Y-%m')";
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @param  list<string>  $months
     * @return array<string, array<string, list<int|string>>>
     */
    private function series($rows, array $months): array
    {
        $series = [];

        foreach (Currency::cases() as $currency) {
            $byMonth = $rows->where('currency', $currency->value)->keyBy('month');
            $series[$currency->value] = [
                'quotations' => array_map(fn (string $month) => (int) ($byMonth[$month]->aggregate ?? 0), $months),
                'quoted' => array_map(fn (string $month) => Money::of($byMonth[$month]->quoted ?? 0), $months),
                'approved' => array_map(fn (string $month) => Money::of($byMonth[$month]->approved ?? 0), $months),
            ];
        }

        return $series;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function followUps(array $filters, bool $overdue): int
    {
        return $this->openFollowUps(Quotation::query(), $filters, $overdue, [
            QuotationStatus::Rejected->value,
            QuotationStatus::Cancelled->value,
        ]) + $this->openFollowUps(Order::query(), $filters, $overdue, [
            OrderStatus::Cancelled->value,
            OrderStatus::Completed->value,
        ]);
    }

    /**
     * @param  list<string>  $closed
     * @param  array<string, mixed>  $filters
     */
    private function openFollowUps(Builder $query, array $filters, bool $overdue, array $closed): int
    {
        $query->whereNotNull('next_follow_up_at')
            ->whereNotIn('status', $closed)
            ->when($filters['client_id'] ?? null, fn (Builder $query, string $id) => $query->where('client_id', $id))
            ->when($filters['user_id'] ?? null, fn (Builder $query, string $id) => $query->where('user_id', $id));

        if ($overdue) {
            $query->where('next_follow_up_at', '<', now());
        }

        if (! $overdue) {
            $query->where('next_follow_up_at', '>=', now());
        }

        $query->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('next_follow_up_at', '>=', $date));
        $query->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('next_follow_up_at', '<=', $date));

        return $query->count();
    }
}
