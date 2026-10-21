<?php

declare(strict_types=1);

namespace App\Features\Shared\Application;

use App\Exceptions\CrmRuleException;
use App\Support\Money;

final class CalculateDocumentTotals
{
    public function __construct(private readonly CalculateLine $line) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{lines: list<array<string, mixed>>, subtotal: string, discount_total: string, tax_total: string, total: string}
     */
    public function __invoke(array $items): array
    {
        if ($items === []) {
            throw new CrmRuleException('La operación debe incluir al menos un concepto.');
        }

        $lines = [];
        $subtotal = Money::zero();
        $discountTotal = Money::zero();
        $taxTotal = Money::zero();
        $total = Money::zero();

        foreach (array_values($items) as $position => $item) {
            $line = ($this->line)($item);
            $line['position'] = $position;
            $lines[] = $line;
            $subtotal = Money::add($subtotal, $line['subtotal']);
            $discountTotal = Money::add($discountTotal, $line['discount_amount']);
            $taxTotal = Money::add($taxTotal, $line['tax_amount']);
            $total = Money::add($total, $line['total']);
        }

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'total' => $total,
        ];
    }
}
