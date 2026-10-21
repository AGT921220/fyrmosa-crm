<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Features\Quotations\Application\ExpireQuotations;
use Illuminate\Console\Command;

class ExpireQuotationsCommand extends Command
{
    protected $signature = 'crm:expire-quotations';

    protected $description = 'Marca como vencidas las cotizaciones cuya fecha de vencimiento ya pasó';

    public function handle(ExpireQuotations $expire): int
    {
        $count = $expire();
        $this->info('Cotizaciones vencidas: '.$count);

        return self::SUCCESS;
    }
}
