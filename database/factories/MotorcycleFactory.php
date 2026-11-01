<?php

namespace Database\Factories;

use App\Models\Motorcycle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Motorcycle>
 */
class MotorcycleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $models = [
            'Honda' => ['Beat', 'Vario 160', 'PCX 160', 'Scoopy'],
            'Yamaha' => ['NMAX 155', 'Aerox 155', 'Mio M3', 'XSR 155'],
            'Suzuki' => ['Satria F150', 'Nex II', 'Address'],
            'Kawasaki' => ['Ninja 250', 'W175', 'KLX 150'],
        ];

        $brand = fake()->randomElement(array_keys($models));

        return [
            'user_id' => User::factory(),
            'brand' => $brand,
            'model' => fake()->randomElement($models[$brand]),
            'year' => fake()->numberBetween(2015, now()->year),
            'license_plate' => Str::upper(fake()->bothify('B #### ???')),
            'odometer_current' => fake()->numberBetween(0, 80000),
            'notes' => null,
        ];
    }
}
