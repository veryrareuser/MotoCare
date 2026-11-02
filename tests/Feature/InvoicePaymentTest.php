<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ServiceOrderStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Motorcycle;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\ServiceType;
use App\Models\Sparepart;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
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

    public function test_cashier_records_partial_payments_until_the_invoice_is_paid(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $invoice = $this->invoice();
        $url = '/staff/invoices/'.$invoice->id;

        $this->actingAs($cashier)
            ->post($url.'/payments', [
                'amount' => 75000,
                'payment_method' => PaymentMethod::Cash->value,
                'notes' => 'Pembayaran pertama.',
            ])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $payment = Payment::query()->firstOrFail();

        $this->assertSame('PAY-20261102-001', $payment->payment_reference);
        $this->assertSame('75000.00', $payment->amount);
        $this->assertSame(PaymentMethod::Cash, $payment->payment_method);
        $this->assertSame(PaymentStatus::Paid, $payment->payment_status);
        $this->assertSame($cashier->id, $payment->received_by);
        $this->assertSame('Pembayaran pertama.', $payment->notes);
        $this->assertNotNull($payment->paid_at);

        $invoice->refresh();

        $this->assertSame('75000.00', $invoice->paid_amount);
        $this->assertSame('125000.00', $invoice->remaining_amount);
        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->payment_status);

        $this->actingAs($cashier)
            ->post($url.'/payments', [
                'amount' => 125000,
                'payment_method' => PaymentMethod::Transfer->value,
            ])
            ->assertRedirect($url)
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertSame('200000.00', $invoice->paid_amount);
        $this->assertSame('0.00', $invoice->remaining_amount);
        $this->assertSame(InvoiceStatus::Paid, $invoice->payment_status);
        $this->assertSame(2, $invoice->payments()->count());
        $this->assertSame('PAY-20261102-002', Payment::query()->latest('id')->firstOrFail()->payment_reference);
    }

    public function test_overpayment_is_rejected_without_creating_a_payment(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $invoice = $this->invoice();
        $url = '/staff/invoices/'.$invoice->id;

        $this->actingAs($cashier)
            ->from($url)
            ->post($url.'/payments', [
                'amount' => 200001,
                'payment_method' => PaymentMethod::Cash->value,
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('payments', 0);

        $invoice->refresh();

        $this->assertSame('0.00', $invoice->paid_amount);
        $this->assertSame('200000.00', $invoice->remaining_amount);
        $this->assertSame(InvoiceStatus::Unpaid, $invoice->payment_status);
    }

    public function test_payment_amount_and_method_are_validated(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $invoice = $this->invoice();
        $url = '/staff/invoices/'.$invoice->id;

        $this->actingAs($cashier)
            ->from($url)
            ->post($url.'/payments', [
                'amount' => 0,
                'payment_method' => PaymentMethod::Cash->value,
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('amount');

        $this->actingAs($cashier)
            ->from($url)
            ->post($url.'/payments', [
                'amount' => 10000,
                'payment_method' => 'bitcoin',
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_a_paid_invoice_rejects_further_payments(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $invoice = $this->invoice([
            'paid_amount' => 200000,
            'remaining_amount' => 0,
            'payment_status' => InvoiceStatus::Paid,
        ]);
        $url = '/staff/invoices/'.$invoice->id;

        $this->actingAs($cashier)
            ->from($url)
            ->post($url.'/payments', [
                'amount' => 1000,
                'payment_method' => PaymentMethod::Cash->value,
            ])
            ->assertRedirect($url)
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame('200000.00', $invoice->fresh()->paid_amount);
    }

    public function test_only_the_cashier_can_record_payments(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $owner = User::factory()->role(UserRole::Owner)->create();
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $customer = User::factory()->create();
        $invoice = $this->invoice();
        $url = '/staff/invoices/'.$invoice->id;

        foreach ([$admin, $owner, $mechanic, $customer] as $blocked) {
            $this->actingAs($blocked)
                ->post($url.'/payments', [
                    'amount' => 10000,
                    'payment_method' => PaymentMethod::Cash->value,
                ])
                ->assertForbidden();
        }

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_service_and_part_lines_are_locked_once_the_invoice_has_a_payment(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 7000,
        ]);
        $serviceType = ServiceType::factory()->create([
            'name' => 'Servis ringan',
            'base_price' => 100000,
        ]);
        $sparepart = Sparepart::factory()->create([
            'name' => 'Filter oli',
            'unit_price' => 50000,
            'stock_quantity' => 10,
        ]);

        $this->actingAs($admin)->post('/staff/service-orders', [
            'motorcycle_id' => $motorcycle->id,
            'service_type_ids' => [$serviceType->id],
            'odometer_in' => 7050,
        ])->assertSessionHasNoErrors();

        $serviceOrder = ServiceOrder::query()->firstOrFail();
        $invoice = $serviceOrder->invoice()->firstOrFail();
        $orderUrl = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($mechanic)
            ->from($orderUrl)
            ->post($orderUrl.'/parts', [
                'sparepart_id' => $sparepart->id,
                'part_quantity' => 1,
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($cashier)
            ->post('/staff/invoices/'.$invoice->id.'/payments', [
                'amount' => 50000,
                'payment_method' => PaymentMethod::Cash->value,
            ])
            ->assertSessionHasNoErrors();

        $serviceLine = $serviceOrder->services()->firstOrFail();
        $partLine = $serviceOrder->parts()->firstOrFail();

        $this->actingAs($mechanic)
            ->from($orderUrl)
            ->delete($orderUrl.'/services/'.$serviceLine->id)
            ->assertRedirect($orderUrl)
            ->assertSessionHasErrors('service');

        $this->actingAs($mechanic)
            ->from($orderUrl)
            ->delete($orderUrl.'/parts/'.$partLine->id)
            ->assertRedirect($orderUrl)
            ->assertSessionHasErrors('part');

        $this->assertDatabaseHas('service_order_services', ['id' => $serviceLine->id]);
        $this->assertDatabaseHas('service_order_parts', ['id' => $partLine->id]);
        $this->assertSame('9.00', $sparepart->fresh()->stock_quantity);
        $this->assertSame('150000.00', $invoice->fresh()->total_amount);
        $this->assertSame('50000.00', $invoice->fresh()->paid_amount);
    }

    public function test_deposit_flow_from_check_in_to_full_settlement(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $customer = User::factory()->create(['name' => 'Budi Santoso']);
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 30000,
        ]);
        $serviceType = ServiceType::factory()->create([
            'name' => 'Servis besar',
            'base_price' => 300000,
        ]);

        $this->actingAs($admin)->post('/staff/service-orders', [
            'motorcycle_id' => $motorcycle->id,
            'service_type_ids' => [$serviceType->id],
            'odometer_in' => 30050,
        ])->assertSessionHasNoErrors();

        $serviceOrder = ServiceOrder::query()->firstOrFail();
        $invoice = $serviceOrder->invoice()->firstOrFail();
        $invoiceUrl = '/staff/invoices/'.$invoice->id;
        $orderUrl = '/staff/service-orders/'.$serviceOrder->id;

        $this->actingAs($cashier)
            ->patch($invoiceUrl.'/deposit', ['deposit_required' => 1])
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertSame('150000.00', $invoice->deposit_amount);

        $this->actingAs($cashier)
            ->post($invoiceUrl.'/payments', [
                'amount' => 150000,
                'payment_method' => PaymentMethod::Qris->value,
                'notes' => 'Deposit servis 50%.',
            ])
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertSame('150000.00', $invoice->paid_amount);
        $this->assertSame('150000.00', $invoice->remaining_amount);
        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->payment_status);

        $this->actingAs($mechanic)->from($orderUrl)
            ->patch($orderUrl.'/status', ['status' => ServiceOrderStatus::BeingInspected->value])
            ->assertSessionHasNoErrors();

        $this->actingAs($mechanic)->from($orderUrl)
            ->patch($orderUrl.'/status', ['status' => ServiceOrderStatus::InProgress->value])
            ->assertSessionHasNoErrors();

        $this->actingAs($mechanic)->from($orderUrl)
            ->patch($orderUrl.'/status', [
                'status' => ServiceOrderStatus::Completed->value,
                'odometer_out' => 30120,
            ])
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertFalse($invoice->isDraft());
        $this->assertTrue($invoice->deposit_required);
        $this->assertSame('300000.00', $invoice->total_amount);
        $this->assertSame('150000.00', $invoice->remaining_amount);
        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->payment_status);

        $this->actingAs($cashier)
            ->post($invoiceUrl.'/payments', [
                'amount' => 150000,
                'payment_method' => PaymentMethod::Transfer->value,
                'notes' => 'Pelunasan.',
            ])
            ->assertSessionHasNoErrors();

        $invoice->refresh();

        $this->assertSame(InvoiceStatus::Paid, $invoice->payment_status);
        $this->assertSame('0.00', $invoice->remaining_amount);

        $this->actingAs($customer)
            ->get('/invoices/'.$invoice->id.'/receipt')
            ->assertOk()
            ->assertSee('PAY-20261102-001')
            ->assertSee('PAY-20261102-002')
            ->assertSee('Lunas')
            ->assertSee('Riwayat pembayaran');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function invoice(array $attributes = []): Invoice
    {
        $customer = User::factory()->create();
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
            ], $attributes));
    }
}
