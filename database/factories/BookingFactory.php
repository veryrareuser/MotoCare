<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Motorcycle;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Rotate the default slot so several bookings never share the same slot.
     */
    protected static int $slotSequence = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $index = static::$slotSequence++;
        $scheduledAt = CarbonImmutable::now(config('app.display_timezone'))
            ->startOfDay()
            ->addDays(1 + intdiv($index, 9))
            ->setTime(8 + ($index % 9), 0);

        return [
            'booking_number' => 'BK-'.fake()->unique()->numerify('########-####'),
            'user_id' => User::factory(),
            'motorcycle_id' => Motorcycle::factory(),
            'booking_slot_id' => BookingSlot::factory()->state([
                'slot_date' => $scheduledAt->toDateString(),
                'start_time' => $scheduledAt->format('H:i:s'),
                'end_time' => $scheduledAt->addHour()->format('H:i:s'),
            ]),
            'scheduled_at' => $scheduledAt->utc(),
            'queue_date' => $scheduledAt->toDateString(),
            'queue_number' => 'A-'.fake()->unique()->numerify('###'),
            'status' => BookingStatus::Confirmed,
            'customer_note' => null,
            'confirmed_at' => now(),
        ];
    }

    /**
     * Indicate that the booking was cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Dibatalkan pelanggan.',
        ]);
    }

    /**
     * Indicate that the booking was checked in.
     */
    public function checkedIn(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BookingStatus::CheckedIn,
            'checked_in_at' => now(),
        ]);
    }
}
