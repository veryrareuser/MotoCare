<?php

namespace Tests\Feature;

use App\Enums\ServiceOrderStatus;
use App\Enums\UserRole;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderService;
use App\Models\ServiceType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderWorkflowTest extends TestCase
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

    public function test_mechanic_can_move_a_work_order_through_every_status(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder(odometer: 20000);
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->patch($url.'/status', [
                'status' => ServiceOrderStatus::BeingInspected->value,
                'notes' => 'Mulai pemeriksaan awal.',
            ])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $serviceOrder->refresh();

        $this->assertSame(ServiceOrderStatus::BeingInspected, $serviceOrder->status);
        $this->assertNotNull($serviceOrder->started_at);

        $this->assertDatabaseHas('service_status_logs', [
            'service_order_id' => $serviceOrder->id,
            'changed_by' => $mechanic->id,
            'old_status' => ServiceOrderStatus::WaitingQueue->value,
            'new_status' => ServiceOrderStatus::BeingInspected->value,
            'notes' => 'Mulai pemeriksaan awal.',
        ]);

        $startedAt = $serviceOrder->started_at;

        $this->actingAs($mechanic)
            ->from($url)
            ->patch($url.'/status', ['status' => ServiceOrderStatus::InProgress->value])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $serviceOrder->refresh();

        $this->assertSame(ServiceOrderStatus::InProgress, $serviceOrder->status);
        $this->assertTrue($startedAt->equalTo($serviceOrder->started_at));

        $this->actingAs($mechanic)
            ->from($url)
            ->patch($url.'/status', [
                'status' => ServiceOrderStatus::Completed->value,
                'odometer_out' => 20080,
                'notes' => 'Servis selesai.',
            ])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $serviceOrder->refresh();

        $this->assertSame(ServiceOrderStatus::Completed, $serviceOrder->status);
        $this->assertSame(20080, $serviceOrder->odometer_out);
        $this->assertNotNull($serviceOrder->completed_at);
        $this->assertSame(20080, $serviceOrder->motorcycle->fresh()->odometer_current);

        $this->actingAs($mechanic)
            ->from($url)
            ->patch($url.'/status', ['status' => ServiceOrderStatus::PickedUp->value])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $serviceOrder->refresh();

        $this->assertSame(ServiceOrderStatus::PickedUp, $serviceOrder->status);
        $this->assertNotNull($serviceOrder->picked_up_at);
        $this->assertSame(4, $serviceOrder->statusLogs()->count());
    }

    public function test_status_cannot_skip_steps_or_move_backwards(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder();
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->patch($url.'/status', ['status' => ServiceOrderStatus::InProgress->value])
            ->assertRedirect($url)
            ->assertSessionHasErrors('status');

        $this->assertSame(ServiceOrderStatus::WaitingQueue, $serviceOrder->fresh()->status);

        $serviceOrder->update(['status' => ServiceOrderStatus::InProgress]);

        $this->actingAs($mechanic)
            ->from($url)
            ->patch($url.'/status', ['status' => ServiceOrderStatus::BeingInspected->value])
            ->assertRedirect($url)
            ->assertSessionHasErrors('status');

        $serviceOrder->update(['status' => ServiceOrderStatus::Completed]);

        $this->actingAs($mechanic)
            ->from($url)
            ->patch($url.'/status', ['status' => ServiceOrderStatus::InProgress->value])
            ->assertRedirect($url)
            ->assertSessionHasErrors('status');

        $this->assertSame(ServiceOrderStatus::Completed, $serviceOrder->fresh()->status);
        $this->assertSame(0, $serviceOrder->statusLogs()->count());
    }

    public function test_completion_requires_an_odometer_out(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder(attributes: ['status' => ServiceOrderStatus::InProgress]);
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->patch($url.'/status', ['status' => ServiceOrderStatus::Completed->value])
            ->assertRedirect($url)
            ->assertSessionHasErrors('odometer_out');

        $serviceOrder->refresh();

        $this->assertSame(ServiceOrderStatus::InProgress, $serviceOrder->status);
        $this->assertNull($serviceOrder->completed_at);
    }

    public function test_completion_rejects_an_odometer_below_the_odometer_in(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder(
            attributes: ['status' => ServiceOrderStatus::InProgress],
            odometer: 20000,
        );
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->patch($url.'/status', [
                'status' => ServiceOrderStatus::Completed->value,
                'odometer_out' => 19999,
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('odometer_out');

        $serviceOrder->refresh();

        $this->assertSame(ServiceOrderStatus::InProgress, $serviceOrder->status);
        $this->assertNull($serviceOrder->odometer_out);
        $this->assertSame(20000, $serviceOrder->motorcycle->fresh()->odometer_current);
    }

    public function test_completion_rejects_an_odometer_below_the_motorcycle_reading(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder(
            attributes: ['status' => ServiceOrderStatus::InProgress],
            odometer: 20000,
        );
        $serviceOrder->motorcycle->update(['odometer_current' => 20500]);
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->patch($url.'/status', [
                'status' => ServiceOrderStatus::Completed->value,
                'odometer_out' => 20400,
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('odometer_out');

        $this->assertSame(ServiceOrderStatus::InProgress, $serviceOrder->fresh()->status);
        $this->assertSame(20500, $serviceOrder->motorcycle->fresh()->odometer_current);
    }

    public function test_cashier_cannot_change_the_work_order_status(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $serviceOrder = $this->workOrder();

        $this->actingAs($cashier)
            ->patch('/staff/service-orders/'.$serviceOrder->id.'/status', [
                'status' => ServiceOrderStatus::BeingInspected->value,
            ])
            ->assertForbidden();

        $this->assertSame(ServiceOrderStatus::WaitingQueue, $serviceOrder->fresh()->status);
    }

    public function test_services_can_be_added_and_removed_while_the_work_order_is_open(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder();
        $serviceType = ServiceType::factory()->create([
            'name' => 'Setel rem',
            'base_price' => 60000,
            'estimated_duration_minutes' => 45,
        ]);
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->post($url.'/services', [
                'service_type_id' => $serviceType->id,
                'quantity' => 2,
            ])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $line = $serviceOrder->services()->firstOrFail();

        $this->assertSame('Setel rem', $line->description);
        $this->assertSame('2.00', $line->quantity);
        $this->assertSame('60000.00', $line->unit_price_snapshot);
        $this->assertSame(45, $line->duration_snapshot_minutes);
        $this->assertSame(120000.0, $line->lineTotal());

        $this->actingAs($mechanic)
            ->from($url)
            ->delete($url.'/services/'.$line->id)
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('service_order_services', ['id' => $line->id]);
    }

    public function test_services_cannot_be_added_after_completion(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $this->workOrder(attributes: [
            'status' => ServiceOrderStatus::Completed,
            'odometer_out' => 10050,
            'completed_at' => now(),
        ]);
        $serviceType = ServiceType::factory()->create();
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($url)
            ->post($url.'/services', [
                'service_type_id' => $serviceType->id,
                'quantity' => 1,
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('service_type_id');

        $this->assertSame(0, $serviceOrder->services()->count());
    }

    public function test_cashier_cannot_record_services(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $serviceOrder = $this->workOrder();
        $serviceType = ServiceType::factory()->create();

        $this->actingAs($cashier)
            ->post('/staff/service-orders/'.$serviceOrder->id.'/services', [
                'service_type_id' => $serviceType->id,
                'quantity' => 1,
            ])
            ->assertForbidden();

        $this->assertSame(0, $serviceOrder->services()->count());
    }

    public function test_work_order_detail_shows_the_recorded_service_lines(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $serviceOrder = $this->workOrder();
        ServiceOrderService::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'description' => 'Servis besar',
            'quantity' => 1,
            'unit_price_snapshot' => 250000,
        ]);

        $this->actingAs($admin)
            ->get('/staff/service-orders/'.$serviceOrder->id)
            ->assertOk()
            ->assertSee('Servis besar')
            ->assertSee('Rp 250.000');
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
