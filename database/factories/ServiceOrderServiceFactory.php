<?php

namespace Database\Factories;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderService;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceOrderService>
 */
class ServiceOrderServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_order_id' => ServiceOrder::factory(),
            'service_type_id' => ServiceType::factory(),
            'description' => 'Servis rutin',
            'quantity' => 1,
            'unit_price_snapshot' => 75000,
            'duration_snapshot_minutes' => 60,
        ];
    }
}
