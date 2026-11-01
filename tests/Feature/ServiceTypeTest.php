<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_service_type(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $response = $this->actingAs($admin)->post('/staff/services', [
            'name' => 'Servis ringan',
            'description' => 'Pemeriksaan menyeluruh.',
            'base_price' => 75000,
            'estimated_duration_minutes' => 60,
            'is_active' => '1',
        ]);

        $response
            ->assertRedirect('/staff/services')
            ->assertSessionHasNoErrors();

        $serviceType = ServiceType::query()->firstOrFail();

        $this->assertSame('Servis ringan', $serviceType->name);
        $this->assertSame('75000.00', $serviceType->base_price);
        $this->assertSame(60, $serviceType->estimated_duration_minutes);
        $this->assertTrue($serviceType->is_active);
    }

    public function test_service_type_can_be_created_without_a_duration(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($admin)
            ->post('/staff/services', [
                'name' => 'Konsultasi',
                'base_price' => 0,
                'estimated_duration_minutes' => null,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull(ServiceType::query()->firstOrFail()->estimated_duration_minutes);
    }

    public function test_admin_can_update_a_service_type(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $serviceType = ServiceType::factory()->create([
            'name' => 'Servis ringan',
            'base_price' => 75000,
        ]);

        $this->actingAs($admin)
            ->patch('/staff/services/'.$serviceType->id, [
                'name' => 'Servis ringan plus',
                'base_price' => 90000,
                'estimated_duration_minutes' => 75,
                'is_active' => '1',
            ])
            ->assertRedirect('/staff/services')
            ->assertSessionHasNoErrors();

        $this->assertSame('Servis ringan plus', $serviceType->fresh()->name);
        $this->assertSame('90000.00', $serviceType->fresh()->base_price);
    }

    public function test_admin_can_retire_a_service_type(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $serviceType = ServiceType::factory()->create();

        $this->actingAs($admin)
            ->delete('/staff/services/'.$serviceType->id)
            ->assertRedirect('/staff/services');

        $this->assertSoftDeleted($serviceType);
    }

    public function test_service_type_requires_name_and_price(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($admin)
            ->post('/staff/services', [])
            ->assertSessionHasErrors(['name', 'base_price']);
    }

    public function test_base_price_cannot_be_negative(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($admin)
            ->post('/staff/services', [
                'name' => 'Servis aneh',
                'base_price' => -1000,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('base_price');
    }

    public function test_staff_can_view_the_service_catalog(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceType = ServiceType::factory()->create(['name' => 'Tune up']);

        $this->actingAs($mechanic)
            ->get('/staff/services')
            ->assertOk()
            ->assertSee('Tune up');
    }

    public function test_non_admin_roles_cannot_manage_service_types(): void
    {
        $roles = [UserRole::Mechanic, UserRole::InventoryStaff, UserRole::Cashier, UserRole::Owner];

        foreach ($roles as $role) {
            $user = User::factory()->role($role)->create();

            $this->actingAs($user)->get('/staff/services/create')->assertForbidden();
            $this->actingAs($user)->post('/staff/services', [
                'name' => 'Tidak boleh',
                'base_price' => 1000,
                'is_active' => '1',
            ])->assertForbidden();
        }

        $this->assertDatabaseCount('service_types', 0);
    }

    public function test_customers_cannot_access_the_staff_service_catalog(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/staff/services')->assertForbidden();
    }
}
