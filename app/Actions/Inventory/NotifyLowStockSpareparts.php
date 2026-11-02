<?php

namespace App\Actions\Inventory;

use App\Enums\UserRole;
use App\Models\Sparepart;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Carbon\CarbonImmutable;

class NotifyLowStockSpareparts
{
    /**
     * Notify inventory staff and admins about spareparts at or below minimum stock.
     *
     * One notification is created per user, sparepart, and local day so the
     * daily scheduler does not repeat the same alert.
     *
     * @return int Number of notifications created.
     */
    public function handle(?CarbonImmutable $today = null): int
    {
        $today ??= CarbonImmutable::now(config('app.display_timezone'))->startOfDay();
        $start = $today->startOfDay()->utc();
        $end = $today->addDay()->startOfDay()->utc();

        $recipients = User::query()
            ->whereIn('role', [UserRole::Admin->value, UserRole::InventoryStaff->value])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($recipients->isEmpty()) {
            return 0;
        }

        $sent = 0;

        Sparepart::query()
            ->active()
            ->lowStock()
            ->orderBy('name')
            ->each(function (Sparepart $sparepart) use ($recipients, $start, $end, &$sent): void {
                foreach ($recipients as $recipient) {
                    if ($this->alreadyNotified($recipient, $sparepart, $start, $end)) {
                        continue;
                    }

                    $recipient->notify(new LowStockAlert($sparepart));
                    $sent++;
                }
            });

        return $sent;
    }

    private function alreadyNotified(
        User $recipient,
        Sparepart $sparepart,
        CarbonImmutable $start,
        CarbonImmutable $end,
    ): bool {
        return $recipient->notifications()
            ->where('type', LowStockAlert::class)
            ->where('data->related_type', 'sparepart')
            ->where('data->related_id', $sparepart->getKey())
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->exists();
    }
}
