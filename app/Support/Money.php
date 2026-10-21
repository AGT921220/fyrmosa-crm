<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Currency;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final class Money
{
    public static function of(string|int|float|null $value, int $scale = 2): string
    {
        if ($value === null || $value === '') {
            return self::zero($scale);
        }

        $normalized = str_replace([',', ' '], '', (string) $value);

        return (string) BigDecimal::of($normalized)->toScale($scale, RoundingMode::HalfUp);
    }

    public static function zero(int $scale = 2): string
    {
        return (string) BigDecimal::zero()->toScale($scale, RoundingMode::HalfUp);
    }

    public static function add(string $left, string $right, int $scale = 2): string
    {
        return (string) BigDecimal::of($left)->plus($right)->toScale($scale, RoundingMode::HalfUp);
    }

    public static function sub(string $left, string $right, int $scale = 2): string
    {
        return (string) BigDecimal::of($left)->minus($right)->toScale($scale, RoundingMode::HalfUp);
    }

    public static function mul(string $left, string $right, int $scale = 2): string
    {
        return (string) BigDecimal::of($left)->multipliedBy($right)->toScale($scale, RoundingMode::HalfUp);
    }

    public static function div(string $left, string $right, int $scale = 6): string
    {
        return (string) BigDecimal::of($left)->dividedBy($right, $scale, RoundingMode::HalfUp);
    }

    public static function gt(string $left, string $right): bool
    {
        return BigDecimal::of($left)->isGreaterThan($right);
    }

    public static function display(string|int|float|null $value, Currency|string|null $currency = null): string
    {
        $amount = number_format((float) self::of($value, 2), 2, '.', ',');
        $code = $currency instanceof Currency ? $currency->label() : ($currency ? strtoupper($currency) : null);

        return $code ? $code.' '.$amount : $amount;
    }
}
