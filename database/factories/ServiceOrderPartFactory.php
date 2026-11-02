<?php

namespace Database\Factories;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderPart;
use App\Models\Sparepart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceOrderPart>
 */
class ServiceOrderPartFactory extends Factory
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
            'sparepart_id' => Sparepart::factory(),
            'quantity' => 1,
            'unit_price_snapshot' => 55000,
            'inventory_movement_id' => null,
        ];
    }
}
