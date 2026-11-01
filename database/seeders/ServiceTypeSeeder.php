<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Seed the workshop service catalog.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Servis ringan',
                'description' => 'Pemeriksaan menyeluruh, pengecekan cairan, dan penyetelan dasar.',
                'base_price' => 75000,
                'estimated_duration_minutes' => 60,
            ],
            [
                'name' => 'Servis besar',
                'description' => 'Bongkar komponen utama, pembersihan, dan penggantian part habis pakai.',
                'base_price' => 250000,
                'estimated_duration_minutes' => 180,
            ],
            [
                'name' => 'Ganti oli mesin',
                'description' => 'Penggantian oli mesin beserta pengecekan kebocoran.',
                'base_price' => 50000,
                'estimated_duration_minutes' => 30,
            ],
            [
                'name' => 'Setel rem',
                'description' => 'Penyetelan rem depan dan belakang serta pengecekan kampas.',
                'base_price' => 60000,
                'estimated_duration_minutes' => 45,
            ],
            [
                'name' => 'Tune up',
                'description' => 'Pembersihan karburator atau injektor dan penyetelan performa mesin.',
                'base_price' => 175000,
                'estimated_duration_minutes' => 120,
            ],
        ];

        foreach ($services as $service) {
            ServiceType::query()->updateOrCreate(
                ['name' => $service['name']],
                [
                    ...$service,
                    'is_active' => true,
                ],
            );
        }
    }
}
