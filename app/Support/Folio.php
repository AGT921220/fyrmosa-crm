<?php

declare(strict_types=1);

namespace App\Support;

final class Folio
{
    public static function format(string $prefix, int $number, int $version = 1): string
    {
        $folio = sprintf('%s-%06d', $prefix, $number);

        if ($version > 1) {
            return $folio.'-V'.$version;
        }

        return $folio;
    }
}
