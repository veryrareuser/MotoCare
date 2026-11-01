<?php

namespace Database\Factories;

use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = [
            'Servis ringan' => [75000, 60],
            'Servis besar' => [250000, 180],
            'Ganti oli mesin' => [50000, 30],
            'Setel rem' => [60000, 45],
            'Ganti busi' => [40000, 20],
            'Tune up' => [175000, 120],
        ];

        $name = fake()->randomElement(array_keys($services));

        return [
            'name' => $name,
            'description' => 'Pengerjaan '.strtolower($name).' oleh mekanik workshop.',
            'base_price' => $services[$name][0],
            'estimated_duration_minutes' => $services[$name][1],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the service is retired from the catalog.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
