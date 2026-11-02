<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Budi Santoso', 'email' => 'customer@motocare.test', 'role' => UserRole::Customer],
            ['name' => 'Siti Rahma', 'email' => 'admin@motocare.test', 'role' => UserRole::Admin],
            ['name' => 'Agus Pratama', 'email' => 'mechanic@motocare.test', 'role' => UserRole::Mechanic],
            ['name' => 'Dewi Lestari', 'email' => 'inventory@motocare.test', 'role' => UserRole::InventoryStaff],
            ['name' => 'Rina Kartika', 'email' => 'cashier@motocare.test', 'role' => UserRole::Cashier],
            ['name' => 'Hendra Wijaya', 'email' => 'owner@motocare.test', 'role' => UserRole::Owner],
        ];

        foreach ($users as $user) {
            $account = User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => 'password',
                    'role' => $user['role'],
                    'is_active' => true,
                ],
            );

            $account->markEmailAsVerified();
        }

        $this->call([
            MotorcycleSeeder::class,
            ServiceTypeSeeder::class,
            SparepartSeeder::class,
            WorkshopScheduleSeeder::class,
            BookingSeeder::class,
            ServiceOrderSeeder::class,
        ]);
    }
}
