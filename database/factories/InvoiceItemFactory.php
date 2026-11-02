<?php

namespace Database\Factories;

use App\Enums\InvoiceItemType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ServiceType;
use App\Models\Sparepart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $unitPrice = fake()->numberBetween(25000, 250000);

        return [
            'invoice_id' => Invoice::factory(),
            'item_type' => InvoiceItemType::Service,
            'description' => fake()->sentence(3),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $quantity * $unitPrice,
            'service_type_id' => null,
            'sparepart_id' => null,
        ];
    }

    /**
     * Indicate that the item references a service type.
     */
    public function forServiceType(ServiceType $serviceType): static
    {
        return $this->state(fn (array $attributes): array => [
            'item_type' => InvoiceItemType::Service,
            'description' => $serviceType->name,
            'unit_price' => $serviceType->base_price,
            'line_total' => (float) $attributes['quantity'] * (float) $serviceType->base_price,
            'service_type_id' => $serviceType->getKey(),
            'sparepart_id' => null,
        ]);
    }

    /**
     * Indicate that the item references a sparepart.
     */
    public function forSparepart(Sparepart $sparepart): static
    {
        return $this->state(fn (array $attributes): array => [
            'item_type' => InvoiceItemType::Sparepart,
            'description' => $sparepart->name,
            'unit_price' => $sparepart->unit_price,
            'line_total' => (float) $attributes['quantity'] * (float) $sparepart->unit_price,
            'service_type_id' => null,
            'sparepart_id' => $sparepart->getKey(),
        ]);
    }
}
