<?php

namespace App\Actions\Reminders;

use App\Enums\MaintenanceReminderStatus;
use App\Models\MaintenanceReminder;
use App\Models\User;
use App\Notifications\MaintenanceReminderDue;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class NotifyDueMaintenanceReminders
{
    /**
     * Send an in-app notification for every scheduled reminder that is due.
     *
     * A reminder is marked as sent inside the same transaction as the
     * notification, so running the scheduler twice does not duplicate it.
     *
     * @return int Number of reminders sent.
     */
    public function handle(?CarbonImmutable $today = null): int
    {
        $today ??= CarbonImmutable::now(config('app.display_timezone'))->startOfDay();
        $sent = 0;

        MaintenanceReminder::query()
            ->with(['motorcycle.customer'])
            ->due($today)
            ->orderBy('id')
            ->each(function (MaintenanceReminder $reminder) use (&$sent): void {
                $motorcycle = $reminder->motorcycle;
                $customer = $motorcycle?->customer;

                if ($motorcycle === null || $motorcycle->trashed() || ! $customer instanceof User || ! $customer->is_active) {
                    return;
                }

                DB::transaction(function () use ($reminder, $customer, &$sent): void {
                    $locked = MaintenanceReminder::query()
                        ->whereKey($reminder->getKey())
                        ->lockForUpdate()
                        ->first();

                    if (! $locked instanceof MaintenanceReminder
                        || $locked->status !== MaintenanceReminderStatus::Scheduled) {
                        return;
                    }

                    $customer->notify(new MaintenanceReminderDue($locked));

                    $locked->update([
                        'status' => MaintenanceReminderStatus::Sent,
                        'sent_at' => now(),
                    ]);

                    $sent++;
                });
            });

        return $sent;
    }
}
