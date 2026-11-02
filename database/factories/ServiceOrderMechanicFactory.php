<?php

namespace Database\Factories;

use App\Enums\MechanicAssignmentRole;
use App\Enums\UserRole;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMechanic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceOrderMechanic>
 */
class ServiceOrderMechanicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_order_id' => ServiceOrder::factory(),
            'mechanic_id' => User::factory()->role(UserRole::Mechanic),
            'assignment_role' => MechanicAssignmentRole::Lead,
            'assigned_at' => now(),
            'unassigned_at' => null,
        ];
    }
}
