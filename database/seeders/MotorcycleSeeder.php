<?php

namespace Database\Seeders;

use App\Models\Motorcycle;
use App\Models\User;
use Illuminate\Database\Seeder;

class MotorcycleSeeder extends Seeder
{
    /**
     * Seed the demo customer's motorcycles.
     */
    public function run(): void
    {
        $customer = User::query()->where('email', 'customer@motocare.test')->first();

        if (! $customer) {
            return;
        }

        $motorcycles = [
            [
                'brand' => 'Honda',
                'model' => 'Vario 160',
                'year' => 2023,
                'license_plate' => 'B 1234 XYZ',
                'odometer_current' => 12450,
                'notes' => 'Servis rutin setiap 4.000 km.',
            ],
            [
                'brand' => 'Yamaha',
                'model' => 'NMAX 155',
                'year' => 2021,
                'license_plate' => 'D 5678 ABC',
                'odometer_current' => 5320,
                'notes' => null,
            ],
        ];

        foreach ($motorcycles as $motorcycle) {
            Motorcycle::query()->updateOrCreate(
                [
                    'user_id' => $customer->getKey(),
                    'license_plate' => $motorcycle['license_plate'],
                ],
                $motorcycle,
            );
        }
    }
}
