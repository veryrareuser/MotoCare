<?php

namespace Database\Factories;

use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Models\ServiceStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceStatusLog>
 */
class ServiceStatusLogFactory extends Factory
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
            'changed_by' => User::factory(),
            'old_status' => null,
            'new_status' => ServiceOrderStatus::WaitingQueue,
            'notes' => 'Work order dibuat.',
        ];
    }
}
