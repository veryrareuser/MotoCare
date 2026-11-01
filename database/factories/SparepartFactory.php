<?php

namespace Database\Factories;

use App\Models\Sparepart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sparepart>
 */
class SparepartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $parts = [
            ['Oli mesin 10W-40', 'botol', 55000, 24],
            ['Filter oli', 'pcs', 35000, 12],
            ['Busi iridium', 'pcs', 85000, 8],
            ['Kampas rem depan', 'set', 95000, 6],
            ['Rantai dan gir', 'set', 275000, 4],
            ['Ban dalam', 'pcs', 45000, 10],
        ];

        [$name, $unit, $price, $minimum] = fake()->randomElement($parts);

        return [
            'sku' => fake()->unique()->bothify('MTC-####-???'),
            'name' => $name,
            'description' => 'Sparepart '.strtolower($name).' untuk perawatan rutin.',
            'unit' => $unit,
            'unit_price' => $price,
            'stock_quantity' => fake()->numberBetween(0, 40),
            'minimum_stock' => $minimum,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the sparepart is at or below its minimum stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock_quantity' => 2,
            'minimum_stock' => 5,
        ]);
    }

    /**
     * Indicate that the sparepart is hidden from the catalog.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
