<?php

namespace Tests\Feature;

use App\Enums\InventoryMovementType;
use App\Enums\UserRole;
use App\Models\InventoryMovement;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_in_increases_the_balance_and_records_a_movement(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create([
            'unit' => 'botol',
            'stock_quantity' => 10,
        ]);

        $this->actingAs($inventory)
            ->post('/staff/spareparts/'.$sparepart->id.'/movements', [
                'movement_type' => InventoryMovementType::In->value,
                'quantity' => 5,
                'notes' => 'Kiriman supplier',
            ])
            ->assertRedirect('/staff/spareparts/'.$sparepart->id)
            ->assertSessionHasNoErrors();

        $this->assertSame('15.00', $sparepart->fresh()->stock_quantity);

        $movement = InventoryMovement::query()->firstOrFail();

        $this->assertSame(InventoryMovementType::In, $movement->movement_type);
        $this->assertSame('5.00', $movement->quantity);
        $this->assertSame('10.00', $movement->quantity_before);
        $this->assertSame('15.00', $movement->quantity_after);
        $this->assertSame($inventory->id, $movement->performed_by);
        $this->assertSame('Kiriman supplier', $movement->notes);
    }

    public function test_adjustment_can_decrease_the_balance(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 10]);

        $this->actingAs($inventory)
            ->post('/staff/spareparts/'.$sparepart->id.'/movements', [
                'movement_type' => InventoryMovementType::Adjustment->value,
                'direction' => 'decrease',
                'quantity' => 4,
                'notes' => 'Selisih hitung',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('6.00', $sparepart->fresh()->stock_quantity);
        $this->assertSame('4.00', InventoryMovement::query()->firstOrFail()->quantity);
    }

    public function test_adjustment_can_increase_the_balance(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 3]);

        $this->actingAs($inventory)
            ->post('/staff/spareparts/'.$sparepart->id.'/movements', [
                'movement_type' => InventoryMovementType::Adjustment->value,
                'direction' => 'increase',
                'quantity' => 2,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('5.00', $sparepart->fresh()->stock_quantity);
    }

    public function test_stock_cannot_go_negative(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 2]);

        $this->actingAs($inventory)
            ->from('/staff/spareparts/'.$sparepart->id)
            ->post('/staff/spareparts/'.$sparepart->id.'/movements', [
                'movement_type' => InventoryMovementType::Adjustment->value,
                'direction' => 'decrease',
                'quantity' => 5,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame('2.00', $sparepart->fresh()->stock_quantity);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_ledger_deltas_match_the_stored_balance(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 0]);

        $this->actingAs($inventory)->post('/staff/spareparts/'.$sparepart->id.'/movements', [
            'movement_type' => InventoryMovementType::In->value,
            'quantity' => 10,
        ]);

        $this->actingAs($inventory)->post('/staff/spareparts/'.$sparepart->id.'/movements', [
            'movement_type' => InventoryMovementType::In->value,
            'quantity' => 5,
        ]);

        $this->actingAs($inventory)->post('/staff/spareparts/'.$sparepart->id.'/movements', [
            'movement_type' => InventoryMovementType::Adjustment->value,
            'direction' => 'decrease',
            'quantity' => 3,
        ]);

        $ledgerDelta = $sparepart->movements()->get()->sum(
            fn (InventoryMovement $movement): float => (float) $movement->quantity_after - (float) $movement->quantity_before,
        );

        $this->assertSame(12.0, $ledgerDelta);
        $this->assertSame(12.0, (float) $sparepart->fresh()->stock_quantity);
        $this->assertSame(3, $sparepart->movements()->count());
    }

    public function test_stock_out_cannot_be_posted_manually(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 10]);

        $this->actingAs($inventory)
            ->post('/staff/spareparts/'.$sparepart->id.'/movements', [
                'movement_type' => InventoryMovementType::Out->value,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('movement_type');

        $this->assertSame('10.00', $sparepart->fresh()->stock_quantity);
    }

    public function test_adjustment_requires_a_direction(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 10]);

        $this->actingAs($inventory)
            ->post('/staff/spareparts/'.$sparepart->id.'/movements', [
                'movement_type' => InventoryMovementType::Adjustment->value,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('direction');
    }

    public function test_quantity_must_be_greater_than_zero(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 10]);

        $this->actingAs($inventory)
            ->post('/staff/spareparts/'.$sparepart->id.'/movements', [
                'movement_type' => InventoryMovementType::In->value,
                'quantity' => 0,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame('10.00', $sparepart->fresh()->stock_quantity);
    }

    public function test_only_inventory_staff_can_post_stock_movements(): void
    {
        $sparepart = Sparepart::factory()->create(['stock_quantity' => 10]);
        $roles = [UserRole::Admin, UserRole::Mechanic, UserRole::Cashier, UserRole::Owner];

        foreach ($roles as $role) {
            $user = User::factory()->role($role)->create();

            $this->actingAs($user)
                ->post('/staff/spareparts/'.$sparepart->id.'/movements', [
                    'movement_type' => InventoryMovementType::In->value,
                    'quantity' => 1,
                ])
                ->assertForbidden();
        }

        $this->assertSame('10.00', $sparepart->fresh()->stock_quantity);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_sparepart_detail_shows_the_stock_ledger(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create([
            'name' => 'Oli mesin 10W-40',
            'stock_quantity' => 10,
        ]);

        InventoryMovement::factory()->create([
            'sparepart_id' => $sparepart->id,
            'performed_by' => $inventory->id,
            'movement_type' => InventoryMovementType::In,
            'quantity' => 10,
            'quantity_before' => 0,
            'quantity_after' => 10,
            'notes' => 'Saldo awal',
        ]);

        $this->actingAs($inventory)
            ->get('/staff/spareparts/'.$sparepart->id)
            ->assertOk()
            ->assertSee('Oli mesin 10W-40')
            ->assertSee('Saldo awal')
            ->assertSee('Stok Masuk');
    }
}
