<?php

namespace Database\Factories;

use App\Enums\MaintenanceReminderStatus;
use App\Enums\UserRole;
use App\Models\MaintenanceReminder;
use App\Models\Motorcycle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceReminder>
 */
class MaintenanceReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'motorcycle_id' => Motorcycle::factory(),
            'created_by' => User::factory()->role(UserRole::Admin),
            'title' => 'Servis rutin '.fake()->numberBetween(4000, 12000).' km',
            'description' => 'Pengingat servis rutin dari bengkel.',
            'due_date' => now()->addDays(7)->toDateString(),
            'due_odometer' => null,
            'status' => MaintenanceReminderStatus::Scheduled,
            'sent_at' => null,
        ];
    }

    public function dueByDate(?string $date = null): static
    {
        return $this->state(fn (): array => [
            'due_date' => $date ?? now()->toDateString(),
            'due_odometer' => null,
        ]);
    }

    public function dueByOdometer(int $odometer): static
    {
        return $this->state(fn (): array => [
            'due_date' => null,
            'due_odometer' => $odometer,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => MaintenanceReminderStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => MaintenanceReminderStatus::Completed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => MaintenanceReminderStatus::Cancelled,
        ]);
    }

    public function forMotorcycle(Motorcycle $motorcycle): static
    {
        return $this->state(fn (): array => [
            'motorcycle_id' => $motorcycle->getKey(),
        ]);
    }
}
