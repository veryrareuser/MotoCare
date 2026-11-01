<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparepartTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_staff_can_create_a_sparepart(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();

        $response = $this->actingAs($inventory)->post('/staff/spareparts', [
            'sku' => ' oil-10w40 ',
            'name' => 'Oli mesin 10W-40',
            'description' => 'Oli sintetik.',
            'unit' => 'botol',
            'unit_price' => 55000,
            'minimum_stock' => 10,
            'is_active' => '1',
        ]);

        $sparepart = Sparepart::query()->firstOrFail();

        $response
            ->assertRedirect('/staff/spareparts/'.$sparepart->id)
            ->assertSessionHasNoErrors();

        $this->assertSame('OIL-10W40', $sparepart->sku);
        $this->assertSame('0.00', $sparepart->stock_quantity);
        $this->assertSame(10, $sparepart->minimum_stock);
    }

    public function test_sparepart_sku_must_be_unique(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        Sparepart::factory()->create(['sku' => 'OIL-10W40']);

        $this->actingAs($inventory)
            ->post('/staff/spareparts', [
                'sku' => 'oil-10w40',
                'name' => 'Oli lain',
                'unit' => 'botol',
                'unit_price' => 50000,
                'minimum_stock' => 5,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('sku');
    }

    public function test_inventory_staff_can_update_a_sparepart_without_touching_stock(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create([
            'stock_quantity' => 12,
            'unit_price' => 50000,
        ]);

        $this->actingAs($inventory)
            ->patch('/staff/spareparts/'.$sparepart->id, [
                'sku' => $sparepart->sku,
                'name' => $sparepart->name,
                'unit' => $sparepart->unit,
                'unit_price' => 60000,
                'minimum_stock' => 4,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('60000.00', $sparepart->fresh()->unit_price);
        $this->assertSame('12.00', $sparepart->fresh()->stock_quantity);
    }

    public function test_inventory_staff_can_retire_a_sparepart(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create();

        $this->actingAs($inventory)
            ->delete('/staff/spareparts/'.$sparepart->id)
            ->assertRedirect('/staff/spareparts');

        $this->assertSoftDeleted($sparepart);
    }

    public function test_admin_can_view_spareparts_but_cannot_manage_them(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $sparepart = Sparepart::factory()->create(['name' => 'Kampas rem depan']);

        $this->actingAs($admin)
            ->get('/staff/spareparts')
            ->assertOk()
            ->assertSee('Kampas rem depan');

        $this->actingAs($admin)->get('/staff/spareparts/create')->assertForbidden();
        $this->actingAs($admin)->post('/staff/spareparts', [])->assertForbidden();
        $this->actingAs($admin)->patch('/staff/spareparts/'.$sparepart->id, [])->assertForbidden();
    }

    public function test_mechanic_can_view_spareparts_but_not_manage_them(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $sparepart = Sparepart::factory()->create();

        $this->actingAs($mechanic)
            ->get('/staff/spareparts/'.$sparepart->id)
            ->assertOk();

        $this->actingAs($mechanic)->get('/staff/spareparts/create')->assertForbidden();
    }

    public function test_low_stock_filter_lists_only_spareparts_at_or_below_minimum(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        Sparepart::factory()->create([
            'name' => 'Busi iridium',
            'stock_quantity' => 2,
            'minimum_stock' => 5,
        ]);
        Sparepart::factory()->create([
            'name' => 'Oli mesin',
            'stock_quantity' => 30,
            'minimum_stock' => 5,
        ]);

        $this->actingAs($inventory)
            ->get('/staff/spareparts?low_stock=1')
            ->assertOk()
            ->assertSee('Busi iridium')
            ->assertDontSee('Oli mesin');
    }

    public function test_search_matches_name_or_sku(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        Sparepart::factory()->create(['name' => 'Busi iridium', 'sku' => 'SPK-IRD']);
        Sparepart::factory()->create(['name' => 'Oli mesin', 'sku' => 'OIL-10W40']);

        $this->actingAs($inventory)
            ->get('/staff/spareparts?search=SPK')
            ->assertOk()
            ->assertSee('Busi iridium')
            ->assertDontSee('Oli mesin');
    }

    public function test_customer_catalog_only_shows_active_spareparts(): void
    {
        $customer = User::factory()->create();
        Sparepart::factory()->create(['name' => 'Busi iridium', 'is_active' => true]);
        Sparepart::factory()->create(['name' => 'Rantai lama', 'is_active' => false]);

        $this->actingAs($customer)
            ->get('/spareparts')
            ->assertOk()
            ->assertSee('Busi iridium')
            ->assertDontSee('Rantai lama');
    }

    public function test_staff_cannot_access_the_customer_catalog_route(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();

        $this->actingAs($inventory)->get('/spareparts')->assertForbidden();
    }

    public function test_inventory_staff_can_add_a_compatible_motorcycle(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create();

        $this->actingAs($inventory)
            ->post('/staff/spareparts/'.$sparepart->id.'/compatibilities', [
                'brand' => 'honda',
                'model' => '  vario 160 ',
                'year_from' => 2020,
                'year_to' => 2024,
            ])
            ->assertRedirect('/staff/spareparts/'.$sparepart->id)
            ->assertSessionHasNoErrors();

        $compatibility = $sparepart->compatibilities()->firstOrFail();

        $this->assertSame('Honda', $compatibility->brand);
        $this->assertSame('vario 160', $compatibility->model);
        $this->assertSame(2020, $compatibility->year_from);
        $this->assertSame(2024, $compatibility->year_to);
    }

    public function test_compatibility_year_range_must_be_coherent(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create();

        $this->actingAs($inventory)
            ->post('/staff/spareparts/'.$sparepart->id.'/compatibilities', [
                'brand' => 'Honda',
                'model' => 'Beat',
                'year_from' => 2022,
                'year_to' => 2018,
            ])
            ->assertSessionHasErrors('year_to');

        $this->assertDatabaseCount('motorcycle_compatibilities', 0);
    }

    public function test_inventory_staff_can_remove_a_compatibility(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create();
        $compatibility = $sparepart->compatibilities()->create([
            'brand' => 'Yamaha',
            'model' => 'NMAX 155',
        ]);

        $this->actingAs($inventory)
            ->delete('/staff/spareparts/'.$sparepart->id.'/compatibilities/'.$compatibility->id)
            ->assertRedirect('/staff/spareparts/'.$sparepart->id);

        $this->assertDatabaseMissing('motorcycle_compatibilities', ['id' => $compatibility->id]);
    }

    public function test_compatibility_from_another_sparepart_cannot_be_removed(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $sparepart = Sparepart::factory()->create();
        $other = Sparepart::factory()->create();
        $compatibility = $other->compatibilities()->create([
            'brand' => 'Suzuki',
            'model' => 'Satria F150',
        ]);

        $this->actingAs($inventory)
            ->delete('/staff/spareparts/'.$sparepart->id.'/compatibilities/'.$compatibility->id)
            ->assertNotFound();

        $this->assertDatabaseHas('motorcycle_compatibilities', ['id' => $compatibility->id]);
    }

    public function test_non_inventory_staff_cannot_manage_compatibilities(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $sparepart = Sparepart::factory()->create();

        $this->actingAs($admin)
            ->post('/staff/spareparts/'.$sparepart->id.'/compatibilities', [
                'brand' => 'Honda',
                'model' => 'Beat',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('motorcycle_compatibilities', 0);
    }
}
