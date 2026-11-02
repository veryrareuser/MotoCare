<?php

namespace Database\Factories;

use App\Enums\MechanicAssignmentRole;
use App\Enums\ServiceOrderStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceOrder>
 */
class ServiceOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_order_number' => 'WO-'.fake()->unique()->numerify('########-####'),
            'booking_id' => null,
            'customer_id' => User::factory(),
            'motorcycle_id' => fn (array $attributes): int => Motorcycle::factory()
                ->create(['user_id' => $attributes['customer_id']])
                ->getKey(),
            'odometer_in' => fake()->numberBetween(1000, 50000),
            'odometer_out' => null,
            'status' => ServiceOrderStatus::WaitingQueue,
            'diagnosis' => null,
            'work_notes' => null,
            'checked_in_at' => now(),
            'started_at' => null,
            'completed_at' => null,
            'picked_up_at' => null,
        ];
    }

    /**
     * Indicate that the work order belongs to a booking.
     */
    public function forBooking(Booking $booking): static
    {
        return $this->state(fn (): array => [
            'booking_id' => $booking->getKey(),
            'customer_id' => $booking->user_id,
            'motorcycle_id' => $booking->motorcycle_id,
            'odometer_in' => $booking->motorcycle->odometer_current,
            'checked_in_at' => now(),
        ]);
    }

    /**
     * Indicate that the work order was created for a walk-in customer.
     */
    public function walkIn(): static
    {
        return $this->state(fn (array $attributes): array => [
            'booking_id' => null,
        ]);
    }

    /**
     * Indicate that inspection has started.
     */
    public function beingInspected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ServiceOrderStatus::BeingInspected,
            'started_at' => now(),
        ]);
    }

    /**
     * Indicate that work is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ServiceOrderStatus::InProgress,
            'started_at' => now(),
        ]);
    }

    /**
     * Indicate that the work order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ServiceOrderStatus::Completed,
            'odometer_out' => fn (array $attributes): int => (int) $attributes['odometer_in'] + 100,
            'started_at' => now(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the motorcycle has been picked up.
     */
    public function pickedUp(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ServiceOrderStatus::PickedUp,
            'odometer_out' => fn (array $attributes): int => (int) $attributes['odometer_in'] + 100,
            'started_at' => now(),
            'completed_at' => now(),
            'picked_up_at' => now(),
        ]);
    }

    /**
     * Attach an active mechanic assignment after the work order is created.
     */
    public function withMechanic(
        User $mechanic,
        MechanicAssignmentRole $role = MechanicAssignmentRole::Lead,
    ): static {
        return $this->afterCreating(function (ServiceOrder $serviceOrder) use ($mechanic, $role): void {
            $serviceOrder->mechanicAssignments()->create([
                'mechanic_id' => $mechanic->getKey(),
                'assignment_role' => $role,
                'assigned_at' => now(),
            ]);
        });
    }

    /**
     * Create a mechanic user and assign them to the work order.
     */
    public function withNewMechanic(
        MechanicAssignmentRole $role = MechanicAssignmentRole::Lead,
    ): static {
        return $this->afterCreating(function (ServiceOrder $serviceOrder) use ($role): void {
            $serviceOrder->mechanicAssignments()->create([
                'mechanic_id' => User::factory()->role(UserRole::Mechanic)->create()->getKey(),
                'assignment_role' => $role,
                'assigned_at' => now(),
            ]);
        });
    }
}
