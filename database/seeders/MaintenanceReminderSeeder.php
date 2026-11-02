<?php

namespace Database\Seeders;

use App\Enums\MaintenanceReminderStatus;
use App\Models\MaintenanceReminder;
use App\Models\Motorcycle;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class MaintenanceReminderSeeder extends Seeder
{
    /**
     * Seed manual maintenance reminders for the demo customer.
     */
    public function run(): void
    {
        $customer = User::query()->where('email', 'customer@motocare.test')->first();
        $admin = User::query()->where('email', 'admin@motocare.test')->first();

        if (! $customer instanceof User || ! $admin instanceof User) {
            return;
        }

        $vario = $customer->motorcycles()->where('license_plate', 'B 1234 XYZ')->first();
        $nmax = $customer->motorcycles()->where('license_plate', 'D 5678 ABC')->first();

        if (! $vario instanceof Motorcycle || ! $nmax instanceof Motorcycle) {
            return;
        }

        $today = CarbonImmutable::now(config('app.display_timezone'))->startOfDay();

        MaintenanceReminder::query()->updateOrCreate(
            [
                'motorcycle_id' => $vario->getKey(),
                'title' => 'Servis rutin 16.000 km',
            ],
            [
                'created_by' => $admin->getKey(),
                'description' => 'Servis rutin berikutnya sekaligus pengecekan rem dan ban.',
                'due_date' => $today->addDays(30)->toDateString(),
                'due_odometer' => 16000,
                'status' => MaintenanceReminderStatus::Scheduled,
                'sent_at' => null,
            ],
        );

        MaintenanceReminder::query()->updateOrCreate(
            [
                'motorcycle_id' => $nmax->getKey(),
                'title' => 'Ganti oli berikutnya',
            ],
            [
                'created_by' => $admin->getKey(),
                'description' => 'Oli mesin sudah waktunya diganti. Segera booking servis.',
                'due_date' => $today->subDays(2)->toDateString(),
                'due_odometer' => 8000,
                'status' => MaintenanceReminderStatus::Sent,
                'sent_at' => $today->subDay()->setTime(7, 0),
            ],
        );

        MaintenanceReminder::query()->updateOrCreate(
            [
                'motorcycle_id' => $vario->getKey(),
                'title' => 'Servis 12.000 km',
            ],
            [
                'created_by' => $admin->getKey(),
                'description' => 'Pengingat servis sebelumnya yang sudah ditindaklanjuti.',
                'due_date' => $today->subDays(60)->toDateString(),
                'due_odometer' => 12000,
                'status' => MaintenanceReminderStatus::Completed,
                'sent_at' => $today->subDays(61)->setTime(7, 0),
            ],
        );
    }
}
