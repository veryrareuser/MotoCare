<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->numberBetween(50000, 500000);

        return [
            'service_order_id' => ServiceOrder::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('########-###'),
            'subtotal' => $total,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $total,
            'deposit_required' => false,
            'deposit_percentage' => null,
            'deposit_amount' => null,
            'paid_amount' => 0,
            'remaining_amount' => $total,
            'payment_status' => InvoiceStatus::Unpaid,
            'issued_at' => null,
            'finalized_at' => null,
        ];
    }

    /**
     * Indicate that the invoice is still a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'issued_at' => null,
            'finalized_at' => null,
        ]);
    }

    /**
     * Indicate that the invoice has been finalized.
     */
    public function finalized(): static
    {
        return $this->state(fn (array $attributes): array => [
            'issued_at' => now(),
            'finalized_at' => now(),
        ]);
    }

    /**
     * Indicate that part of the invoice has been paid.
     */
    public function partiallyPaid(): static
    {
        return $this->state(function (array $attributes): array {
            $total = (float) $attributes['total_amount'];
            $paid = round($total / 2, 2);

            return [
                'paid_amount' => $paid,
                'remaining_amount' => round($total - $paid, 2),
                'payment_status' => InvoiceStatus::PartiallyPaid,
                'issued_at' => now(),
                'finalized_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the invoice is fully paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'paid_amount' => $attributes['total_amount'],
            'remaining_amount' => 0,
            'payment_status' => InvoiceStatus::Paid,
            'issued_at' => now(),
            'finalized_at' => now(),
        ]);
    }

    /**
     * Indicate that the invoice requires a 50% deposit.
     */
    public function withDeposit(): static
    {
        return $this->state(fn (array $attributes): array => [
            'deposit_required' => true,
            'deposit_percentage' => 50,
            'deposit_amount' => round((float) $attributes['total_amount'] / 2, 2),
        ]);
    }

    /**
     * Attach the invoice to an existing work order.
     */
    public function forServiceOrder(ServiceOrder $serviceOrder): static
    {
        return $this->state(fn (): array => [
            'service_order_id' => $serviceOrder->getKey(),
        ]);
    }
}
