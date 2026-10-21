<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Features\Shared\Application\CalculateDocumentTotals;
use App\Features\Shared\Application\CalculateLine;
use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_rounds_half_up_and_calculates_line_totals(): void
    {
        $this->assertSame('1.01', Money::of('1.005', 2));
        $this->assertSame('28.80', Money::mul('180.00', Money::div('16', '100', 6), 2));

        $totals = (new CalculateDocumentTotals(new CalculateLine))([
            [
                'description' => 'Servicio',
                'quantity' => '2',
                'unit' => 'PZA',
                'unit_price' => '100',
                'discount_type' => 'percent',
                'discount_value' => '10',
                'tax_rate' => '16',
            ],
            [
                'description' => 'Instalación',
                'quantity' => '1',
                'unit' => 'SERV',
                'unit_price' => '50',
                'discount_type' => 'amount',
                'discount_value' => '5',
                'tax_rate' => '0',
            ],
        ]);

        $this->assertSame('225.00', $totals['subtotal']);
        $this->assertSame('25.00', $totals['discount_total']);
        $this->assertSame('28.80', $totals['tax_total']);
        $this->assertSame('253.80', $totals['total']);
    }
}
