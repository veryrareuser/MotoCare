<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'payment_reference' => 'PAY-'.fake()->unique()->numerify('########-###'),
            'amount' => fake()->numberBetween(25000, 250000),
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Paid,
            'received_by' => User::factory(),
            'paid_at' => now(),
            'notes' => null,
        ];
    }
}
