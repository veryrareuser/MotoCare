<?php

namespace Tests\Feature;

use App\Enums\ServiceOrderStatus;
use App\Enums\UserRole;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\ServiceType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderTest extends TestCase
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

    public function test_inventory_staff_cannot_access_work_orders(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $serviceOrder = $this->workOrder();

        $this->actingAs($inventory)->get('/staff/service-orders')->assertForbidden();
        $this->actingAs($inventory)->get('/staff/service-orders/'.$serviceOrder->id)->assertForbidden();
    }

    public function test_customer_cannot_access_staff_work_orders(): void
    {
        $customer = User::factory()->create();
        $serviceOrder = $this->workOrder($customer);

        $this->actingAs($customer)->get('/staff/service-orders')->assertForbidden();
        $this->actingAs($customer)->get('/staff/service-orders/'.$serviceOrder->id)->assertForbidden();
    }

    public function test_owner_can_view_work_orders_but_not_change_them(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();
        $serviceOrder = $this->workOrder();
        $serviceType = ServiceType::factory()->create();

        $this->actingAs($owner)
            ->get('/staff/service-orders?date=2026-11-02')
            ->assertOk()
            ->assertSee($serviceOrder->work_order_number);

        $this->actingAs($owner)
            ->get('/staff/service-orders/'.$serviceOrder->id)
            ->assertOk()
            ->assertSee($serviceOrder->work_order_number);

        $this->actingAs($owner)
            ->post('/staff/service-orders', [
                'motorcycle_id' => $serviceOrder->motorcycle_id,
                'service_type_ids' => [$serviceType->id],
                'odometer_in' => $serviceOrder->odometer_in,
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->patch('/staff/service-orders/'.$serviceOrder->id.'/status', [
                'status' => ServiceOrderStatus::BeingInspected->value,
            ])
            ->assertForbidden();

        $this->assertSame(ServiceOrderStatus::WaitingQueue, $serviceOrder->fresh()->status);
    }

    public function test_cashier_can_create_a_walk_in_work_order(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $customer = User::factory()->create(['name' => 'Budi Santoso']);
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 12000,
        ]);
        $serviceTypes = ServiceType::factory()->count(2)->create();

        $response = $this->actingAs($cashier)->post('/staff/service-orders', [
            'motorcycle_id' => $motorcycle->id,
            'service_type_ids' => $serviceTypes->pluck('id')->all(),
            'odometer_in' => 12050,
            'notes' => 'Servis rutin tanpa booking.',
        ]);

        $serviceOrder = ServiceOrder::query()->firstOrFail();

        $response
            ->assertRedirect('/staff/service-orders/'.$serviceOrder->id)
            ->assertSessionHasNoErrors();

        $this->assertNull($serviceOrder->booking_id);
        $this->assertSame($customer->id, $serviceOrder->customer_id);
        $this->assertSame($motorcycle->id, $serviceOrder->motorcycle_id);
        $this->assertSame(12050, $serviceOrder->odometer_in);
        $this->assertSame(ServiceOrderStatus::WaitingQueue, $serviceOrder->status);
        $this->assertSame('WO-20261102-001', $serviceOrder->work_order_number);
        $this->assertNotNull($serviceOrder->checked_in_at);
        $this->assertSame('Servis rutin tanpa booking.', $serviceOrder->work_notes);
        $this->assertSame(2, $serviceOrder->services()->count());

        foreach ($serviceTypes as $serviceType) {
            $this->assertDatabaseHas('service_order_services', [
                'service_order_id' => $serviceOrder->id,
                'service_type_id' => $serviceType->id,
                'description' => $serviceType->name,
                'unit_price_snapshot' => $serviceType->base_price,
            ]);
        }

        $this->assertDatabaseHas('service_status_logs', [
            'service_order_id' => $serviceOrder->id,
            'changed_by' => $cashier->id,
            'old_status' => null,
            'new_status' => ServiceOrderStatus::WaitingQueue->value,
        ]);

        $this->assertSame(12050, $motorcycle->fresh()->odometer_current);
    }

    public function test_walk_in_rejects_an_odometer_below_the_last_reading(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 12000,
        ]);
        $serviceType = ServiceType::factory()->create();

        $this->actingAs($admin)
            ->from('/staff/service-orders/create')
            ->post('/staff/service-orders', [
                'motorcycle_id' => $motorcycle->id,
                'service_type_ids' => [$serviceType->id],
                'odometer_in' => 11999,
            ])
            ->assertRedirect('/staff/service-orders/create')
            ->assertSessionHasErrors('odometer_in');

        $this->assertDatabaseCount('service_orders', 0);
        $this->assertSame(12000, $motorcycle->fresh()->odometer_current);
    }

    public function test_walk_in_rejects_an_inactive_service(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 5000,
        ]);
        $serviceType = ServiceType::factory()->create(['is_active' => false]);

        $this->actingAs($admin)
            ->from('/staff/service-orders/create')
            ->post('/staff/service-orders', [
                'motorcycle_id' => $motorcycle->id,
                'service_type_ids' => [$serviceType->id],
                'odometer_in' => 5000,
            ])
            ->assertRedirect('/staff/service-orders/create')
            ->assertSessionHasErrors('service_type_ids.0');

        $this->assertDatabaseCount('service_orders', 0);
    }

    public function test_staff_can_open_the_walk_in_form(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $customer = User::factory()->create(['name' => 'Budi Santoso']);
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'license_plate' => 'B 1234 XYZ',
        ]);

        $this->actingAs($cashier)
            ->get('/staff/service-orders/create')
            ->assertOk()
            ->assertSee('Work order walk-in')
            ->assertSee('Budi Santoso')
            ->assertSee($motorcycle->license_plate);
    }

    public function test_work_order_index_lists_orders_for_the_selected_date(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $today = $this->workOrder(attributes: ['work_order_number' => 'WO-20261102-001']);
        $otherDay = $this->workOrder(attributes: [
            'work_order_number' => 'WO-20261103-001',
            'checked_in_at' => now()->addDay(),
        ]);

        $this->actingAs($admin)
            ->get('/staff/service-orders?date=2026-11-02')
            ->assertOk()
            ->assertSee($today->work_order_number)
            ->assertDontSee($otherDay->work_order_number);
    }

    public function test_work_order_index_filters_by_search_and_status(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $budi = $this->workOrder(
            User::factory()->create(['name' => 'Budi Santoso']),
            ['work_order_number' => 'WO-20261102-001'],
        );
        $siti = $this->workOrder(
            User::factory()->create(['name' => 'Siti Rahma']),
            [
                'work_order_number' => 'WO-20261102-002',
                'status' => ServiceOrderStatus::InProgress,
            ],
        );

        $this->actingAs($admin)
            ->get('/staff/service-orders?date=2026-11-02&search=Budi')
            ->assertOk()
            ->assertSee($budi->work_order_number)
            ->assertDontSee($siti->work_order_number);

        $this->actingAs($admin)
            ->get('/staff/service-orders?date=2026-11-02&status=in_progress')
            ->assertOk()
            ->assertSee($siti->work_order_number)
            ->assertDontSee($budi->work_order_number);
    }

    public function test_admin_can_assign_release_and_reassign_mechanics(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $mechanicA = User::factory()->role(UserRole::Mechanic)->create(['name' => 'Agus Pratama']);
        $mechanicB = User::factory()->role(UserRole::Mechanic)->create(['name' => 'Joko Susilo']);
        $serviceOrder = $this->workOrder();
        $showUrl = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($admin)
            ->from($showUrl)
            ->post($showUrl.'/mechanics', [
                'mechanic_id' => $mechanicA->id,
                'assignment_role' => 'lead',
            ])
            ->assertRedirect($showUrl)
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->from($showUrl)
            ->post($showUrl.'/mechanics', [
                'mechanic_id' => $mechanicB->id,
                'assignment_role' => 'assistant',
            ])
            ->assertRedirect($showUrl)
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $serviceOrder->activeMechanicAssignments()->count());

        $this->actingAs($admin)
            ->from($showUrl)
            ->post($showUrl.'/mechanics', [
                'mechanic_id' => $mechanicA->id,
            ])
            ->assertRedirect($showUrl)
            ->assertSessionHasErrors('mechanic_id');

        $assignment = $serviceOrder->mechanicAssignments()
            ->where('mechanic_id', $mechanicA->id)
            ->firstOrFail();

        $this->actingAs($admin)
            ->from($showUrl)
            ->delete($showUrl.'/mechanics/'.$assignment->id)
            ->assertRedirect($showUrl)
            ->assertSessionHasNoErrors();

        $this->assertNotNull($assignment->fresh()->unassigned_at);
        $this->assertSame(1, $serviceOrder->activeMechanicAssignments()->count());

        $this->actingAs($admin)
            ->from($showUrl)
            ->post($showUrl.'/mechanics', [
                'mechanic_id' => $mechanicA->id,
                'assignment_role' => 'lead',
            ])
            ->assertRedirect($showUrl)
            ->assertSessionHasNoErrors();

        $this->assertSame(3, $serviceOrder->mechanicAssignments()->count());
        $this->assertSame(2, $serviceOrder->activeMechanicAssignments()->count());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function workOrder(
        ?User $customer = null,
        array $attributes = [],
        int $odometer = 10000,
    ): ServiceOrder {
        $customer ??= User::factory()->create();

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
