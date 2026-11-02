<?php

namespace Database\Seeders;

use App\Models\MaintenanceReminder;
use App\Models\Sparepart;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\MaintenanceReminderDue;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Seed demo in-app notifications for customers and inventory staff.
     */
    public function run(): void
    {
        $customer = User::query()->where('email', 'customer@motocare.test')->first();
        $admin = User::query()->where('email', 'admin@motocare.test')->first();
        $inventory = User::query()->where('email', 'inventory@motocare.test')->first();

        if ($customer instanceof User) {
            $reminder = MaintenanceReminder::query()
                ->where('title', 'Ganti oli berikutnya')
                ->whereHas('motorcycle', fn ($query) => $query->where('user_id', $customer->getKey()))
                ->first();

            if ($reminder instanceof MaintenanceReminder && ! $this->hasNotification($customer, MaintenanceReminderDue::class, 'maintenance_reminder', $reminder->getKey())) {
                $customer->notify(new MaintenanceReminderDue($reminder));
            }
        }

        $sparepart = Sparepart::query()->where('sku', 'SPK-IRD')->first();

        if (! $sparepart instanceof Sparepart) {
            return;
        }

        foreach ([$admin, $inventory] as $recipient) {
            if (! $recipient instanceof User || ! $recipient->is_active) {
                continue;
            }

            if (! $this->hasNotification($recipient, LowStockAlert::class, 'sparepart', $sparepart->getKey())) {
                $recipient->notify(new LowStockAlert($sparepart));
            }
        }
    }

    private function hasNotification(User $user, string $type, string $relatedType, int $relatedId): bool
    {
        return $user->notifications()
            ->where('type', $type)
            ->where('data->related_type', $relatedType)
            ->where('data->related_id', $relatedId)
            ->exists();
    }
}
