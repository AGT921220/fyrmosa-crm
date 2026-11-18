<?php

declare(strict_types=1);

namespace App\Support;

final class FilterValues
{
    /**
     * @return list<string>
     */
    public static function list(mixed $value): array
    {
        return self::clean($value, false);
    }

    /**
     * @return list<string>
     */
    public static function ids(mixed $value): array
    {
        return self::clean($value, true);
    }

    /**
     * @return list<string>
     */
    private static function clean(mixed $value, bool $numeric): array
    {
        $items = is_array($value) ? $value : [$value];
        $clean = [];

        foreach ($items as $item) {
            if (! is_scalar($item)) {
                continue;
            }

            $text = trim((string) $item);

            if ($text === '' || $text === 'all') {
                continue;
            }

            if ($numeric && ! ctype_digit($text)) {
                continue;
            }

            $clean[$text] = $text;
        }

        return array_values($clean);
    }
}
