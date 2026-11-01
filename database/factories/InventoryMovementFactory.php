<?php

namespace Database\Factories;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $before = fake()->numberBetween(10, 60);
        $quantity = fake()->numberBetween(1, 10);

        return [
            'sparepart_id' => Sparepart::factory(),
            'movement_type' => InventoryMovementType::In,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $before + $quantity,
            'reference_type' => null,
            'reference_id' => null,
            'performed_by' => User::factory(),
            'notes' => null,
        ];
    }
}
