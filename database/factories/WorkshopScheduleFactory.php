<?php

namespace Database\Factories;

use App\Models\WorkshopSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkshopSchedule>
 */
class WorkshopScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_of_week' => 1,
            'open_time' => '08:00:00',
            'close_time' => '17:00:00',
            'slot_duration_minutes' => 60,
            'capacity_per_slot' => 2,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the workshop is closed on this day.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
