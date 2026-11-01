<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Motorcycle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotorcycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_motorcycle_list(): void
    {
        $this->get('/motorcycles')->assertRedirect('/login');
    }

    public function test_customer_only_sees_their_own_motorcycles(): void
    {
        $customer = User::factory()->create();
        Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'brand' => 'Honda',
            'model' => 'Beat',
        ]);
        Motorcycle::factory()->create([
            'brand' => 'Yamaha',
            'model' => 'NMAX 155',
        ]);

        $this->actingAs($customer)
            ->get('/motorcycles')
            ->assertOk()
            ->assertSee('Honda')
            ->assertSee('Beat')
            ->assertDontSee('NMAX 155');
    }

    public function test_customer_dashboard_shows_the_registered_motorcycle_count(): void
    {
        $customer = User::factory()->create();
        Motorcycle::factory()->count(2)->create(['user_id' => $customer->id]);

        $this->actingAs($customer)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('2 dari 5 slot terpakai');
    }

    public function test_customer_can_create_a_motorcycle(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)->post('/motorcycles', [
            'brand' => 'Honda',
            'model' => 'Vario 160',
            'year' => 2023,
            'license_plate' => 'b 1234 xyz',
            'odometer_current' => 1200,
            'notes' => 'Motor harian',
        ]);

        $response
            ->assertRedirect('/motorcycles')
            ->assertSessionHasNoErrors();

        $motorcycle = Motorcycle::query()->firstOrFail();

        $this->assertSame($customer->id, $motorcycle->user_id);
        $this->assertSame('Honda', $motorcycle->brand);
        $this->assertSame('B 1234 XYZ', $motorcycle->license_plate);
    }

    public function test_odometer_defaults_to_zero_when_left_empty(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->post('/motorcycles', [
                'brand' => 'Honda',
                'model' => 'Beat',
                'license_plate' => null,
                'odometer_current' => null,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, Motorcycle::query()->firstOrFail()->odometer_current);
    }

    public function test_sixth_motorcycle_is_rejected(): void
    {
        $customer = User::factory()->create();
        Motorcycle::factory()->count(5)->create(['user_id' => $customer->id]);

        $response = $this->actingAs($customer)
            ->from('/motorcycles/create')
            ->post('/motorcycles', [
                'brand' => 'Suzuki',
                'model' => 'Satria F150',
                'year' => 2024,
                'odometer_current' => 0,
            ]);

        $response
            ->assertRedirect('/motorcycles/create')
            ->assertSessionHasErrors('motorcycle');

        $this->assertSame(5, $customer->motorcycles()->count());
    }

    public function test_deleting_a_motorcycle_frees_a_slot(): void
    {
        $customer = User::factory()->create();
        $motorcycles = Motorcycle::factory()->count(5)->create(['user_id' => $customer->id]);
        $deleted = $motorcycles->first();

        $this->actingAs($customer)
            ->delete('/motorcycles/'.$deleted->id)
            ->assertRedirect('/motorcycles');

        $this->assertSoftDeleted($deleted);
        $this->assertSame(4, $customer->motorcycles()->count());

        $this->actingAs($customer)
            ->post('/motorcycles', [
                'brand' => 'Kawasaki',
                'model' => 'W175',
                'odometer_current' => 0,
                'license_plate' => 'B 5555 NEW',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(5, $customer->motorcycles()->count());
    }

    public function test_customer_can_update_their_own_motorcycle(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'brand' => 'Honda',
            'model' => 'Beat',
            'odometer_current' => 1000,
        ]);

        $this->actingAs($customer)
            ->patch('/motorcycles/'.$motorcycle->id, [
                'brand' => 'Honda',
                'model' => 'Beat Street',
                'odometer_current' => 2500,
                'license_plate' => $motorcycle->license_plate,
            ])
            ->assertRedirect('/motorcycles')
            ->assertSessionHasNoErrors();

        $this->assertSame('Beat Street', $motorcycle->fresh()->model);
        $this->assertSame(2500, $motorcycle->fresh()->odometer_current);
    }

    public function test_customer_cannot_edit_update_or_delete_another_customers_motorcycle(): void
    {
        $customer = User::factory()->create();
        $other = Motorcycle::factory()->create([
            'brand' => 'Honda',
            'model' => 'Beat',
        ]);
        $originalBrand = $other->brand;

        $this->actingAs($customer)
            ->get('/motorcycles/'.$other->id.'/edit')
            ->assertForbidden();

        $this->actingAs($customer)
            ->patch('/motorcycles/'.$other->id, [
                'brand' => 'Diubah',
                'model' => 'Pemilik Lain',
                'odometer_current' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($customer)
            ->delete('/motorcycles/'.$other->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted($other);
        $this->assertSame($originalBrand, $other->fresh()->brand);
    }

    public function test_motorcycle_requires_brand_and_model(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->from('/motorcycles/create')
            ->post('/motorcycles', [])
            ->assertSessionHasErrors(['brand', 'model']);
    }

    public function test_year_must_be_within_a_reasonable_range(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->from('/motorcycles/create')
            ->post('/motorcycles', [
                'brand' => 'Honda',
                'model' => 'Beat',
                'year' => 1800,
                'odometer_current' => 0,
            ])
            ->assertSessionHasErrors('year');
    }

    public function test_license_plate_must_be_unique_among_active_motorcycles(): void
    {
        $customer = User::factory()->create();
        Motorcycle::factory()->create(['license_plate' => 'B 9999 ZZZ']);

        $this->actingAs($customer)
            ->from('/motorcycles/create')
            ->post('/motorcycles', [
                'brand' => 'Honda',
                'model' => 'Beat',
                'odometer_current' => 0,
                'license_plate' => 'b 9999 zzz',
            ])
            ->assertSessionHasErrors('license_plate');
    }

    public function test_license_plate_can_be_reused_after_soft_delete(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['license_plate' => 'B 7777 AAA']);
        $motorcycle->delete();

        $this->actingAs($customer)
            ->post('/motorcycles', [
                'brand' => 'Honda',
                'model' => 'Beat',
                'odometer_current' => 0,
                'license_plate' => 'B 7777 AAA',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_staff_cannot_access_customer_motorcycle_routes(): void
    {
        $staff = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($staff)->get('/motorcycles')->assertForbidden();
    }
}
