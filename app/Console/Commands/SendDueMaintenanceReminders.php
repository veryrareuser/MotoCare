<?php

namespace App\Console\Commands;

use App\Actions\Reminders\NotifyDueMaintenanceReminders;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reminders:send-due')]
#[Description('Kirim notifikasi untuk pengingat servis yang jatuh tempo')]
class SendDueMaintenanceReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(NotifyDueMaintenanceReminders $notifyDueMaintenanceReminders): int
    {
        $sent = $notifyDueMaintenanceReminders->handle();

        $this->info($sent.' pengingat jatuh tempo diproses.');

        return self::SUCCESS;
    }
}
