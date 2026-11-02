<?php

namespace Tests\Feature;

use App\Enums\InventoryMovementType;
use App\Enums\ServiceOrderStatus;
use App\Enums\UserRole;
use App\Models\InventoryMovement;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderPart;
use App\Models\Sparepart;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderPartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Monday morning, before the workshop opens.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-11-02 07:00:00', config('app.display_timezone')));
    }

    public function test_adding_a_part_deducts_stock_and_records_a_referenced_movement(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder();
        $sparepart = Sparepart::factory()->create([
            'name' => 'Oli mesin 10W-40',
            'sku' => 'MTC-0001-OIL',
            'unit_price' => 55000,
            'stock_quantity' => 10,
        ]);
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->post($url.'/parts', [
                'sparepart_id' => $sparepart->id,
                'part_quantity' => 3,
            ])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $line = ServiceOrderPart::query()->firstOrFail();

        $this->assertSame($serviceOrder->id, $line->service_order_id);
        $this->assertSame($sparepart->id, $line->sparepart_id);
        $this->assertSame('3.00', $line->quantity);
        $this->assertSame('55000.00', $line->unit_price_snapshot);
        $this->assertNotNull($line->inventory_movement_id);
        $this->assertSame('7.00', $sparepart->fresh()->stock_quantity);

        $movement = $line->inventoryMovement;

        $this->assertNotNull($movement);
        $this->assertSame(InventoryMovementType::Out, $movement->movement_type);
        $this->assertSame('3.00', $movement->quantity);
        $this->assertSame('10.00', $movement->quantity_before);
        $this->assertSame('7.00', $movement->quantity_after);
        $this->assertSame('service_order_part', $movement->reference_type);
        $this->assertSame($line->id, $movement->reference_id);
        $this->assertSame($mechanic->id, $movement->performed_by);
    }

    public function test_a_part_cannot_exceed_the_available_stock(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder();
        $sparepart = Sparepart::factory()->create([
            'stock_quantity' => 2,
            'unit_price' => 45000,
        ]);
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->post($url.'/parts', [
                'sparepart_id' => $sparepart->id,
                'part_quantity' => 3,
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('part_quantity');

        $this->assertSame('2.00', $sparepart->fresh()->stock_quantity);
        $this->assertSame(0, $serviceOrder->parts()->count());
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_removing_a_part_returns_stock_and_posts_an_adjustment(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder();
        $sparepart = Sparepart::factory()->create([
            'stock_quantity' => 10,
            'unit_price' => 55000,
        ]);
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->post($url.'/parts', [
                'sparepart_id' => $sparepart->id,
                'part_quantity' => 3,
            ])
            ->assertSessionHasNoErrors();

        $line = ServiceOrderPart::query()->firstOrFail();

        $this->actingAs($mechanic)
            ->from($url)
            ->delete($url.'/parts/'.$line->id)
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('service_order_parts', ['id' => $line->id]);
        $this->assertSame('10.00', $sparepart->fresh()->stock_quantity);
        $this->assertDatabaseCount('inventory_movements', 2);

        $adjustment = InventoryMovement::query()->orderByDesc('id')->firstOrFail();

        $this->assertSame(InventoryMovementType::Adjustment, $adjustment->movement_type);
        $this->assertSame('3.00', $adjustment->quantity);
        $this->assertSame('7.00', $adjustment->quantity_before);
        $this->assertSame('10.00', $adjustment->quantity_after);
        $this->assertSame('service_order_part_removal', $adjustment->reference_type);
        $this->assertSame($line->id, $adjustment->reference_id);
        $this->assertSame($mechanic->id, $adjustment->performed_by);
    }

    public function test_parts_cannot_be_added_after_completion(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder(attributes: [
            'status' => ServiceOrderStatus::Completed,
            'odometer_out' => 10050,
            'completed_at' => now(),
        ]);
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 10]);
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->post($url.'/parts', [
                'sparepart_id' => $sparepart->id,
                'part_quantity' => 1,
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('sparepart_id');

        $this->assertSame('10.00', $sparepart->fresh()->stock_quantity);
        $this->assertSame(0, $serviceOrder->parts()->count());
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_parts_cannot_be_removed_after_completion(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder(attributes: [
            'status' => ServiceOrderStatus::Completed,
            'odometer_out' => 10050,
            'completed_at' => now(),
        ]);
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 10]);
        $line = ServiceOrderPart::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'sparepart_id' => $sparepart->id,
            'quantity' => 1,
            'unit_price_snapshot' => 55000,
        ]);
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->delete($url.'/parts/'.$line->id)
            ->assertRedirect($url)
            ->assertSessionHasErrors('part');

        $this->assertDatabaseHas('service_order_parts', ['id' => $line->id]);
        $this->assertSame('10.00', $sparepart->fresh()->stock_quantity);
    }

    public function test_inactive_spareparts_are_rejected(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder();
        $sparepart = Sparepart::factory()->create([
            'stock_quantity' => 10,
            'is_active' => false,
        ]);
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->post($url.'/parts', [
                'sparepart_id' => $sparepart->id,
                'part_quantity' => 1,
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('sparepart_id');

        $this->assertSame('10.00', $sparepart->fresh()->stock_quantity);
        $this->assertSame(0, $serviceOrder->parts()->count());
    }

    public function test_cashier_cannot_record_parts(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $serviceOrder = $this->workOrder();
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 10]);

        $this->actingAs($cashier)
            ->post('/staff/service-orders/'.$serviceOrder->id.'/parts', [
                'sparepart_id' => $sparepart->id,
                'part_quantity' => 1,
            ])
            ->assertForbidden();

        $this->assertSame('10.00', $sparepart->fresh()->stock_quantity);
        $this->assertSame(0, $serviceOrder->parts()->count());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function workOrder(
        array $attributes = [],
        int $odometer = 10000,
    ): ServiceOrder {
        $customer = User::factory()->create();

        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => $odometer,
        ]);

        return ServiceOrder::factory()->create([
            'customer_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
            'odometer_in' => $odometer,
            ...$attributes,
        ]);
    }
}
