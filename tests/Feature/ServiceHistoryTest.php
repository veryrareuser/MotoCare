<?php

namespace Tests\Feature;

use App\Enums\MaintenanceReminderStatus;
use App\Enums\ServiceOrderStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\MaintenanceReminder;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderPart;
use App\Models\ServiceOrderService;
use App\Models\ServiceStatusLog;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_only_lists_the_customer_active_work_orders(): void
    {
        $customer = User::factory()->create();
        $motorcycle = $this->motorcycleFor($customer);
        $otherCustomer = User::factory()->create();

        $active = ServiceOrder::factory()->inProgress()->create([
            'customer_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
            'work_order_number' => 'WO-20261102-001',
        ]);

        ServiceOrder::factory()->completed()->create([
            'customer_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
            'work_order_number' => 'WO-20261102-002',
        ]);

        ServiceOrder::factory()->inProgress()->create([
            'customer_id' => $otherCustomer->id,
            'motorcycle_id' => $this->motorcycleFor($otherCustomer)->id,
            'work_order_number' => 'WO-20261102-003',
        ]);

        $this->actingAs($customer)
            ->get('/tracking')
            ->assertOk()
            ->assertSee($active->work_order_number)
            ->assertDontSee('WO-20261102-002')
            ->assertDontSee('WO-20261102-003');
    }

    public function test_service_history_only_lists_completed_and_picked_up_work_orders(): void
    {
        $customer = User::factory()->create();
        $motorcycle = $this->motorcycleFor($customer);

        $completed = ServiceOrder::factory()->completed()->create([
            'customer_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
            'work_order_number' => 'WO-20261102-010',
        ]);

        $pickedUp = ServiceOrder::factory()->pickedUp()->create([
            'customer_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
            'work_order_number' => 'WO-20261102-011',
        ]);

        ServiceOrder::factory()->inProgress()->create([
            'customer_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
            'work_order_number' => 'WO-20261102-012',
        ]);

        $this->actingAs($customer)
            ->get('/service-history')
            ->assertOk()
            ->assertSee($completed->work_order_number)
            ->assertSee($pickedUp->work_order_number)
            ->assertDontSee('WO-20261102-012');
    }

    public function test_customer_can_view_their_own_service_order_detail(): void
    {
        $customer = User::factory()->create();
        $motorcycle = $this->motorcycleFor($customer);
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $sparepart = Sparepart::factory()->create([
            'name' => 'Oli mesin 10W-40',
            'sku' => 'OIL-10W40',
            'unit' => 'botol',
        ]);

        $serviceOrder = ServiceOrder::factory()
            ->completed()
            ->withMechanic($mechanic)
            ->create([
                'customer_id' => $customer->id,
                'motorcycle_id' => $motorcycle->id,
                'work_order_number' => 'WO-20261102-020',
                'diagnosis' => 'Rem depan aus.',
                'work_notes' => 'Rem sudah disetel.',
            ]);

        ServiceOrderService::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'description' => 'Servis besar',
            'quantity' => 1,
            'unit_price_snapshot' => 250000,
            'duration_snapshot_minutes' => 90,
        ]);

        ServiceOrderPart::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'sparepart_id' => $sparepart->id,
            'quantity' => 2,
            'unit_price_snapshot' => 55000,
        ]);

        ServiceStatusLog::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'changed_by' => $mechanic->id,
            'old_status' => ServiceOrderStatus::InProgress,
            'new_status' => ServiceOrderStatus::Completed,
            'notes' => 'Servis selesai.',
        ]);

        $invoice = Invoice::factory()
            ->forServiceOrder($serviceOrder)
            ->partiallyPaid()
            ->create([
                'invoice_number' => 'INV-20261102-020',
            ]);

        $this->actingAs($customer)
            ->get('/service-history/'.$serviceOrder->id)
            ->assertOk()
            ->assertSee('WO-20261102-020')
            ->assertSee('Servis besar')
            ->assertSee('Rp 250.000')
            ->assertSee('Oli mesin 10W-40')
            ->assertSee('Rp 110.000')
            ->assertSee('Rem depan aus.')
            ->assertSee('Rem sudah disetel.')
            ->assertSee('Riwayat status')
            ->assertSee('Servis selesai.')
            ->assertSee($invoice->invoice_number)
            ->assertSee('Dibayar Sebagian');
    }

    public function test_customer_cannot_view_another_customers_service_order(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $serviceOrder = ServiceOrder::factory()->completed()->create([
            'customer_id' => $otherCustomer->id,
            'motorcycle_id' => $this->motorcycleFor($otherCustomer)->id,
        ]);

        $this->actingAs($customer)
            ->get('/service-history/'.$serviceOrder->id)
            ->assertForbidden();
    }

    public function test_staff_cannot_open_customer_service_history(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();

        $this->actingAs($mechanic)
            ->get('/service-history')
            ->assertForbidden();

        $this->actingAs($mechanic)
            ->get('/tracking')
            ->assertForbidden();
    }

    public function test_customer_dashboard_shows_active_service_due_reminders_and_history(): void
    {
        $customer = User::factory()->create();
        $motorcycle = $this->motorcycleFor($customer, 12450);

        $active = ServiceOrder::factory()->inProgress()->create([
            'customer_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
            'work_order_number' => 'WO-20261102-030',
        ]);

        $completed = ServiceOrder::factory()->completed()->create([
            'customer_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
            'work_order_number' => 'WO-20261102-031',
        ]);

        $reminder = MaintenanceReminder::factory()
            ->forMotorcycle($motorcycle)
            ->dueByDate()
            ->create([
                'created_by' => User::factory()->role(UserRole::Admin)->create()->id,
                'title' => 'Ganti oli berikutnya',
                'status' => MaintenanceReminderStatus::Scheduled,
            ]);

        $this->actingAs($customer)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Lihat progres servis')
            ->assertSee($active->work_order_number)
            ->assertSee($reminder->title)
            ->assertSee('Lihat semua pengingat')
            ->assertSee($completed->work_order_number);
    }

    private function motorcycleFor(User $customer, int $odometer = 10000): Motorcycle
    {
        return Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => $odometer,
        ]);
    }
}
