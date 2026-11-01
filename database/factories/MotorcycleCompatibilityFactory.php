<?php

namespace Database\Factories;

use App\Models\MotorcycleCompatibility;
use App\Models\Sparepart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MotorcycleCompatibility>
 */
class MotorcycleCompatibilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $models = [
            'Honda' => ['Beat', 'Vario 160', 'PCX 160'],
            'Yamaha' => ['NMAX 155', 'Aerox 155', 'Mio M3'],
            'Suzuki' => ['Satria F150', 'Nex II'],
            'Kawasaki' => ['Ninja 250', 'W175'],
        ];

        $brand = fake()->randomElement(array_keys($models));

        return [
            'sparepart_id' => Sparepart::factory(),
            'brand' => $brand,
            'model' => fake()->randomElement($models[$brand]),
            'year_from' => null,
            'year_to' => null,
        ];
    }
}
