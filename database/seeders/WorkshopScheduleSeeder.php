<?php

namespace Database\Seeders;

use App\Models\WorkshopSchedule;
use Illuminate\Database\Seeder;

class WorkshopScheduleSeeder extends Seeder
{
    /**
     * Seed the approved operating pattern: Monday to Saturday, 08:00-17:00,
     * 60-minute slots with two motorcycles per slot. Sunday is closed.
     */
    public function run(): void
    {
        for ($day = 1; $day <= 7; $day++) {
            WorkshopSchedule::query()->updateOrCreate(
                ['day_of_week' => $day],
                [
                    'open_time' => '08:00:00',
                    'close_time' => '17:00:00',
                    'slot_duration_minutes' => 60,
                    'capacity_per_slot' => 2,
                    'is_active' => $day <= 6,
                ],
            );
        }
    }
}
