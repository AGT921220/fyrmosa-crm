<?php

declare(strict_types=1);

namespace App\Features\Shared\Application;

use App\Enums\DiscountType;
use App\Exceptions\CrmRuleException;
use App\Support\Money;

final class CalculateLine
{
    /**
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>
     */
    public function __invoke(array $line): array
    {
        $quantity = Money::of($line['quantity'] ?? null, 4);
        $unitPrice = Money::of($line['unit_price'] ?? null, 4);

        if (! Money::gt($quantity, '0')) {
            throw new CrmRuleException('La cantidad debe ser mayor a cero.');
        }

        if (Money::gt('0', $unitPrice)) {
            throw new CrmRuleException('El precio unitario no puede ser negativo.');
        }

        $gross = Money::mul($quantity, $unitPrice, 2);
        $discountType = DiscountType::from($line['discount_type'] ?? DiscountType::Amount->value);
        $discountValue = Money::of($line['discount_value'] ?? 0, 4);
        $discountAmount = $this->discount($discountType, $discountValue, $gross);
        $subtotal = Money::sub($gross, $discountAmount, 2);
        $taxRate = Money::of($line['tax_rate'] ?? 0, 4);

        if (Money::gt('0', $taxRate) || Money::gt($taxRate, '100')) {
            throw new CrmRuleException('El impuesto debe estar entre 0 y 100%.');
        }

        $taxAmount = Money::mul($subtotal, Money::div($taxRate, '100', 6), 2);

        return [
            'sku' => isset($line['sku']) && $line['sku'] !== '' ? $line['sku'] : null,
            'description' => $line['description'],
            'quantity' => $quantity,
            'unit' => $line['unit'],
            'unit_price' => $unitPrice,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'tax_rate' => $taxRate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => Money::add($subtotal, $taxAmount, 2),
        ];
    }

    private function discount(DiscountType $type, string $value, string $gross): string
    {
        if ($type === DiscountType::Percent) {
            if (Money::gt($value, '100')) {
                throw new CrmRuleException('El descuento en porcentaje no puede ser mayor a 100.');
            }

            return Money::mul($gross, Money::div($value, '100', 6), 2);
        }

        $amount = Money::of($value, 2);

        if (Money::gt($amount, $gross)) {
            throw new CrmRuleException('El descuento no puede superar el importe del concepto.');
        }

        return $amount;
    }
}
