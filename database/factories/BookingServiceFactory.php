<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingService>
 */
class BookingServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'service_type_id' => ServiceType::factory(),
            'price_snapshot' => 75000,
            'duration_snapshot_minutes' => 60,
        ];
    }
}
