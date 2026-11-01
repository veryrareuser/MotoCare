<?php

namespace Database\Factories;

use App\Models\BookingSlot;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingSlot>
 */
class BookingSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slotDate = CarbonImmutable::now(config('app.display_timezone'))->addDay()->toDateString();

        return [
            'slot_date' => $slotDate,
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'capacity' => 2,
            'booked_count' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the slot starts at the given hour.
     */
    public function startingAt(int $hour, ?string $date = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'slot_date' => $date ?? $attributes['slot_date'],
            'start_time' => sprintf('%02d:00:00', $hour),
            'end_time' => sprintf('%02d:00:00', $hour + 1),
        ]);
    }

    /**
     * Indicate that the slot has reached its capacity.
     */
    public function full(): static
    {
        return $this->state(fn (array $attributes): array => [
            'booked_count' => $attributes['capacity'],
        ]);
    }

    /**
     * Indicate that the slot is no longer bookable.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
