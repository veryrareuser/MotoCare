<?php

namespace App\Console\Commands;

use App\Actions\Inventory\NotifyLowStockSpareparts;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('inventory:low-stock-alerts')]
#[Description('Kirim notifikasi stok rendah ke staf inventaris dan admin')]
class SendLowStockAlerts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(NotifyLowStockSpareparts $notifyLowStockSpareparts): int
    {
        $sent = $notifyLowStockSpareparts->handle();

        $this->info($sent.' notifikasi stok rendah dibuat.');

        return self::SUCCESS;
    }
}
