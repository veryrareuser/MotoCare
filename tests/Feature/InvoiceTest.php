<?php

namespace Tests\Feature;

use App\Actions\Billing\CreateInvoiceForServiceOrder;
use App\Enums\InvoiceItemType;
use App\Enums\InvoiceStatus;
use App\Enums\ServiceOrderStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\ServiceType;
use App\Models\Sparepart;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
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

    public function test_walk_in_creates_a_draft_invoice_from_service_snapshots(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 12000,
        ]);
        $oilChange = ServiceType::factory()->create([
            'name' => 'Ganti oli mesin',
            'base_price' => 50000,
        ]);
        $tuneUp = ServiceType::factory()->create([
            'name' => 'Tune up',
            'base_price' => 175000,
        ]);

        $response = $this->actingAs($admin)->post('/staff/service-orders', [
            'motorcycle_id' => $motorcycle->id,
            'service_type_ids' => [$oilChange->id, $tuneUp->id],
            'odometer_in' => 12050,
        ]);

        $serviceOrder = ServiceOrder::query()->firstOrFail();
        $invoice = $serviceOrder->invoice()->firstOrFail();

        $response
            ->assertRedirect('/staff/service-orders/'.$serviceOrder->id)
            ->assertSessionHasNoErrors();

        $this->assertSame('INV-20261102-001', $invoice->invoice_number);
        $this->assertTrue($invoice->isDraft());
        $this->assertSame(InvoiceStatus::Unpaid, $invoice->payment_status);
        $this->assertSame('225000.00', $invoice->total_amount);
        $this->assertSame('0.00', $invoice->paid_amount);
        $this->assertSame('225000.00', $invoice->remaining_amount);
        $this->assertSame(2, $invoice->items()->count());

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_type' => InvoiceItemType::Service->value,
            'description' => 'Ganti oli mesin',
            'quantity' => 1,
            'unit_price' => 50000,
            'line_total' => 50000,
            'service_type_id' => $oilChange->id,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_type' => InvoiceItemType::Service->value,
            'description' => 'Tune up',
            'unit_price' => 175000,
            'line_total' => 175000,
            'service_type_id' => $tuneUp->id,
        ]);
    }

    public function test_invoice_items_follow_draft_changes_without_rewriting_snapshots(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 8000,
        ]);
        $firstService = ServiceType::factory()->create([
            'name' => 'Servis ringan',
            'base_price' => 50000,
        ]);
        $secondService = ServiceType::factory()->create([
            'name' => 'Setel rem',
            'base_price' => 60000,
        ]);
        $sparepart = Sparepart::factory()->create([
            'name' => 'Oli mesin 10W-40',
            'unit_price' => 55000,
            'stock_quantity' => 10,
        ]);

        $this->actingAs($admin)->post('/staff/service-orders', [
            'motorcycle_id' => $motorcycle->id,
            'service_type_ids' => [$firstService->id],
            'odometer_in' => 8050,
        ])->assertSessionHasNoErrors();

        $serviceOrder = ServiceOrder::query()->firstOrFail();
        $invoice = $serviceOrder->invoice()->firstOrFail();
        $showUrl = '/staff/service-orders/'.$serviceOrder->id;

        $this->assertSame('50000.00', $invoice->total_amount);

        $firstService->update(['base_price' => 999999]);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'service_type_id' => $firstService->id,
            'unit_price' => 50000,
        ]);
        $this->assertSame('50000.00', $invoice->fresh()->total_amount);

        $this->actingAs($mechanic)
            ->from($showUrl)
            ->post($showUrl.'/services', [
                'service_type_id' => $secondService->id,
                'quantity' => 2,
            ])
            ->assertRedirect($showUrl)
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertSame('170000.00', $invoice->total_amount);
        $this->assertSame(2, $invoice->items()->count());

        $this->actingAs($mechanic)
            ->from($showUrl)
            ->post($showUrl.'/parts', [
                'sparepart_id' => $sparepart->id,
                'part_quantity' => 2,
            ])
            ->assertRedirect($showUrl)
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertSame('280000.00', $invoice->total_amount);
        $this->assertSame(3, $invoice->items()->count());
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_type' => InvoiceItemType::Sparepart->value,
            'description' => 'Oli mesin 10W-40',
            'quantity' => 2,
            'unit_price' => 55000,
            'line_total' => 110000,
            'sparepart_id' => $sparepart->id,
        ]);
        $this->assertSame('8.00', $sparepart->fresh()->stock_quantity);

        $partLine = $serviceOrder->parts()->firstOrFail();

        $this->actingAs($mechanic)
            ->from($showUrl)
            ->delete($showUrl.'/parts/'.$partLine->id)
            ->assertRedirect($showUrl)
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertSame('170000.00', $invoice->total_amount);
        $this->assertSame('10.00', $sparepart->fresh()->stock_quantity);

        $secondLine = $serviceOrder->services()
            ->where('service_type_id', $secondService->id)
            ->firstOrFail();

        $this->actingAs($mechanic)
            ->from($showUrl)
            ->delete($showUrl.'/services/'.$secondLine->id)
            ->assertRedirect($showUrl)
            ->assertSessionHasNoErrors();

        $this->assertSame('50000.00', $invoice->fresh()->total_amount);
        $this->assertSame(1, $invoice->fresh()->items()->count());
    }

    public function test_completing_a_work_order_finalizes_and_freezes_the_invoice(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 15000,
        ]);
        $serviceType = ServiceType::factory()->create([
            'name' => 'Servis besar',
            'base_price' => 250000,
        ]);

        $this->actingAs($admin)->post('/staff/service-orders', [
            'motorcycle_id' => $motorcycle->id,
            'service_type_ids' => [$serviceType->id],
            'odometer_in' => 15050,
        ])->assertSessionHasNoErrors();

        $serviceOrder = ServiceOrder::query()->firstOrFail();
        $invoice = $serviceOrder->invoice()->firstOrFail();
        $url = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)->from($url)
            ->patch($url.'/status', ['status' => ServiceOrderStatus::BeingInspected->value])
            ->assertSessionHasNoErrors();

        $this->actingAs($mechanic)->from($url)
            ->patch($url.'/status', ['status' => ServiceOrderStatus::InProgress->value])
            ->assertSessionHasNoErrors();

        $this->actingAs($mechanic)->from($url)
            ->patch($url.'/status', [
                'status' => ServiceOrderStatus::Completed->value,
                'odometer_out' => 15120,
            ])
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertFalse($invoice->isDraft());
        $this->assertNotNull($invoice->issued_at);
        $this->assertNotNull($invoice->finalized_at);
        $this->assertSame('250000.00', $invoice->total_amount);
        $this->assertSame('250000.00', $invoice->remaining_amount);

        $serviceType->update(['base_price' => 999999]);

        app(CreateInvoiceForServiceOrder::class)->handle($serviceOrder->fresh());

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Servis besar',
            'unit_price' => 250000,
            'line_total' => 250000,
        ]);
        $this->assertSame('250000.00', $invoice->fresh()->total_amount);
    }

    public function test_invoice_numbers_use_a_daily_sequence(): void
    {
        $createInvoice = app(CreateInvoiceForServiceOrder::class);

        $first = $createInvoice->handle(ServiceOrder::factory()->create());
        $second = $createInvoice->handle(ServiceOrder::factory()->create());

        $this->assertSame('INV-20261102-001', $first->invoice_number);
        $this->assertSame('INV-20261102-002', $second->invoice_number);

        $this->travelTo(CarbonImmutable::parse('2026-11-03 08:00:00', config('app.display_timezone')));

        $third = $createInvoice->handle(ServiceOrder::factory()->create());

        $this->assertSame('INV-20261103-001', $third->invoice_number);
    }

    public function test_cashier_can_manage_the_optional_deposit_while_the_invoice_is_draft(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 9000,
        ]);
        $serviceType = ServiceType::factory()->create([
            'name' => 'Servis ringan',
            'base_price' => 200000,
        ]);

        $this->actingAs($admin)->post('/staff/service-orders', [
            'motorcycle_id' => $motorcycle->id,
            'service_type_ids' => [$serviceType->id],
            'odometer_in' => 9050,
        ])->assertSessionHasNoErrors();

        $invoice = ServiceOrder::query()->firstOrFail()->invoice()->firstOrFail();
        $url = '/staff/invoices/'.$invoice->id;

        $this->actingAs($cashier)
            ->patch($url.'/deposit', ['deposit_required' => 1])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertTrue($invoice->deposit_required);
        $this->assertSame('50.00', $invoice->deposit_percentage);
        $this->assertSame('100000.00', $invoice->deposit_amount);

        $this->actingAs($cashier)
            ->patch($url.'/deposit', [
                'deposit_required' => 1,
                'deposit_percentage' => 30,
            ])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $this->assertSame('60000.00', $invoice->fresh()->deposit_amount);

        $this->actingAs($cashier)
            ->patch($url.'/deposit', ['deposit_required' => 0])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertFalse($invoice->deposit_required);
        $this->assertNull($invoice->deposit_percentage);
        $this->assertNull($invoice->deposit_amount);

        $this->actingAs($cashier)
            ->from($url)
            ->patch($url.'/deposit', [
                'deposit_required' => 1,
                'deposit_percentage' => 0,
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('deposit_percentage');

        $this->actingAs($cashier)
            ->from($url)
            ->patch($url.'/deposit', ['deposit_required' => 1])
            ->assertSessionHasNoErrors();

        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $serviceOrder = $invoice->serviceOrder;
        $serviceOrderUrl = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)->from($serviceOrderUrl)
            ->patch($serviceOrderUrl.'/status', ['status' => ServiceOrderStatus::BeingInspected->value])
            ->assertSessionHasNoErrors();

        $this->actingAs($mechanic)->from($serviceOrderUrl)
            ->patch($serviceOrderUrl.'/status', ['status' => ServiceOrderStatus::InProgress->value])
            ->assertSessionHasNoErrors();

        $this->actingAs($mechanic)->from($serviceOrderUrl)
            ->patch($serviceOrderUrl.'/status', [
                'status' => ServiceOrderStatus::Completed->value,
                'odometer_out' => 9080,
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($cashier)
            ->from($url)
            ->patch($url.'/deposit', ['deposit_required' => 0])
            ->assertRedirect($url)
            ->assertSessionHasErrors('deposit_required');
    }

    public function test_customer_can_read_only_their_own_invoices(): void
    {
        $budi = User::factory()->create(['name' => 'Budi Santoso']);
        $siti = User::factory()->create(['name' => 'Siti Rahma']);
        $budiInvoice = $this->invoiceFor($budi, ['invoice_number' => 'INV-20261102-001']);
        $sitiInvoice = $this->invoiceFor($siti, ['invoice_number' => 'INV-20261102-002']);

        $this->actingAs($budi)
            ->get('/invoices')
            ->assertOk()
            ->assertSee($budiInvoice->invoice_number)
            ->assertDontSee($sitiInvoice->invoice_number);

        $this->actingAs($budi)
            ->get('/invoices/'.$budiInvoice->id)
            ->assertOk()
            ->assertSee($budiInvoice->invoice_number);

        $this->actingAs($budi)
            ->get('/invoices/'.$budiInvoice->id.'/receipt')
            ->assertOk()
            ->assertSee($budiInvoice->invoice_number)
            ->assertSee('Kuitansi');

        $this->actingAs($budi)
            ->get('/invoices/'.$sitiInvoice->id)
            ->assertForbidden();

        $this->actingAs($budi)
            ->get('/invoices/'.$sitiInvoice->id.'/receipt')
            ->assertForbidden();

        $this->actingAs($budi)
            ->get('/staff/invoices/'.$budiInvoice->id)
            ->assertForbidden();
    }

    public function test_staff_invoice_access_follows_the_role_matrix(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $owner = User::factory()->role(UserRole::Owner)->create();
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $invoice = $this->invoiceFor(User::factory()->create());
        $url = '/staff/invoices/'.$invoice->id;

        foreach ([$admin, $cashier, $owner] as $viewer) {
            $this->actingAs($viewer)->get('/staff/invoices')->assertOk();
            $this->actingAs($viewer)->get($url)->assertOk()->assertSee($invoice->invoice_number);
            $this->actingAs($viewer)->get($url.'/receipt')->assertOk()->assertSee($invoice->invoice_number);
        }

        foreach ([$mechanic, $inventory] as $blocked) {
            $this->actingAs($blocked)->get('/staff/invoices')->assertForbidden();
            $this->actingAs($blocked)->get($url)->assertForbidden();
            $this->actingAs($blocked)->get($url.'/receipt')->assertForbidden();
        }

        $this->actingAs($admin)
            ->patch($url.'/deposit', ['deposit_required' => 1])
            ->assertForbidden();

        $this->actingAs($owner)
            ->post($url.'/payments', ['amount' => 1000, 'payment_method' => 'cash'])
            ->assertForbidden();
    }

    public function test_staff_invoice_index_filters_by_date_status_and_search(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $budi = User::factory()->create(['name' => 'Budi Santoso']);
        $siti = User::factory()->create(['name' => 'Siti Rahma']);

        $today = $this->invoiceFor($budi, [
            'invoice_number' => 'INV-20261102-001',
            'payment_status' => InvoiceStatus::Unpaid,
        ]);
        $otherDay = $this->invoiceFor($siti, [
            'invoice_number' => 'INV-20261101-001',
            'payment_status' => InvoiceStatus::PartiallyPaid,
            'paid_amount' => 50000,
            'remaining_amount' => 150000,
        ]);
        $otherDay->forceFill(['created_at' => now()->subDay()])->save();

        $this->actingAs($admin)
            ->get('/staff/invoices?date=2026-11-02')
            ->assertOk()
            ->assertSee($today->invoice_number)
            ->assertDontSee($otherDay->invoice_number);

        $this->actingAs($admin)
            ->get('/staff/invoices?date=2026-11-01&status=partially_paid')
            ->assertOk()
            ->assertSee($otherDay->invoice_number)
            ->assertDontSee($today->invoice_number);

        $this->actingAs($admin)
            ->get('/staff/invoices?date=2026-11-02&search=Budi')
            ->assertOk()
            ->assertSee($today->invoice_number);
    }

    /**
     * @param  array<string, mixed>  $invoiceAttributes
     */
    private function invoiceFor(User $customer, array $invoiceAttributes = []): Invoice
    {
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
        ]);

        $serviceOrder = ServiceOrder::factory()->create([
            'customer_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
        ]);

        return Invoice::factory()
            ->forServiceOrder($serviceOrder)
            ->create(array_merge([
                'subtotal' => 200000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 200000,
                'paid_amount' => 0,
                'remaining_amount' => 200000,
                'payment_status' => InvoiceStatus::Unpaid,
                'issued_at' => now(),
                'finalized_at' => now(),
            ], $invoiceAttributes));
    }
}
