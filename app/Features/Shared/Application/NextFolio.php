<?php

declare(strict_types=1);

namespace App\Features\Shared\Application;

use App\Models\FolioSequence;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class NextFolio
{
    public function __invoke(string $prefix): int
    {
        if (DB::transactionLevel() === 0) {
            throw new RuntimeException('El folio debe generarse dentro de una transacción.');
        }

        FolioSequence::query()->insertOrIgnore([
            'prefix' => $prefix,
            'current' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = FolioSequence::query()
            ->where('prefix', $prefix)
            ->lockForUpdate()
            ->first();

        $sequence->current++;
        $sequence->save();

        return $sequence->current;
    }
}
