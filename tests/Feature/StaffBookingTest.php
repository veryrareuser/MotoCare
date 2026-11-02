<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\ServiceOrderStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Invoice;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\ServiceType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffBookingTest extends TestCase
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

    public function test_queue_index_lists_bookings_for_the_selected_date(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $customer = User::factory()->create(['name' => 'Budi Santoso']);
        $booking = $this->confirmedBooking($customer, ['queue_number' => 'A-024']);

        $this->actingAs($admin)
            ->get('/staff/bookings?date=2026-11-02')
            ->assertOk()
            ->assertSee('A-024')
            ->assertSee('Budi Santoso')
            ->assertSee($booking->motorcycle->license_plate)
            ->assertSee($booking->booking_number);
    }

    public function test_queue_index_defaults_to_today_and_hides_other_dates(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $today = $this->confirmedBooking(User::factory()->create(), ['queue_number' => 'A-001']);
        $otherDay = $this->confirmedBooking(User::factory()->create(), [
            'queue_number' => 'A-900',
            'queue_date' => '2026-11-03',
        ], 9, '2026-11-03');

        $this->actingAs($admin)
            ->get('/staff/bookings')
            ->assertOk()
            ->assertSee($today->queue_number)
            ->assertDontSee($otherDay->queue_number);
    }

    public function test_inventory_staff_cannot_view_the_queue(): void
    {
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();

        $this->actingAs($inventory)->get('/staff/bookings')->assertForbidden();
    }

    public function test_owner_can_view_the_queue_but_not_check_in(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();
        $booking = $this->confirmedBooking(User::factory()->create());

        $this->actingAs($owner)
            ->get('/staff/bookings/'.$booking->id)
            ->assertOk();

        $this->actingAs($owner)
            ->post('/staff/bookings/'.$booking->id.'/check-in')
            ->assertForbidden();

        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    public function test_check_in_modal_renders_on_the_booking_detail_page(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $booking = $this->confirmedBooking(User::factory()->create());

        $this->actingAs($mechanic)
            ->get('/staff/bookings/'.$booking->id)
            ->assertOk()
            ->assertSee('Odometer masuk (km)')
            ->assertSee('Check-in & buat work order', escape: false);
    }

    public function test_staff_can_check_a_booking_in(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $booking = $this->confirmedBooking(User::factory()->create());
        $serviceType = ServiceType::factory()->create([
            'name' => 'Servis ringan',
            'base_price' => 75000,
            'estimated_duration_minutes' => 60,
        ]);

        $booking->services()->create([
            'service_type_id' => $serviceType->id,
            'price_snapshot' => 75000,
            'duration_snapshot_minutes' => 60,
        ]);

        $odometerIn = $booking->motorcycle->odometer_current + 25;

        $response = $this->actingAs($mechanic)
            ->post('/staff/bookings/'.$booking->id.'/check-in', ['odometer_in' => $odometerIn]);

        $serviceOrder = ServiceOrder::query()->where('booking_id', $booking->id)->firstOrFail();

        $response
            ->assertRedirect('/staff/service-orders/'.$serviceOrder->id)
            ->assertSessionHasNoErrors();

        $booking->refresh();

        $this->assertSame(BookingStatus::ConvertedToService, $booking->status);
        $this->assertNotNull($booking->checked_in_at);
        $this->assertSame($serviceOrder->id, $booking->serviceOrder?->id);
        $this->assertSame(ServiceOrderStatus::WaitingQueue, $serviceOrder->status);
        $this->assertSame($odometerIn, $serviceOrder->odometer_in);
        $this->assertSame($odometerIn, $booking->motorcycle->fresh()->odometer_current);

        $this->assertDatabaseHas('service_order_services', [
            'service_order_id' => $serviceOrder->id,
            'service_type_id' => $serviceType->id,
            'description' => 'Servis ringan',
            'unit_price_snapshot' => 75000,
            'duration_snapshot_minutes' => 60,
        ]);

        $this->assertDatabaseHas('booking_status_logs', [
            'booking_id' => $booking->id,
            'changed_by' => $mechanic->id,
            'old_status' => BookingStatus::Confirmed->value,
            'new_status' => BookingStatus::ConvertedToService->value,
        ]);

        $this->assertDatabaseHas('service_status_logs', [
            'service_order_id' => $serviceOrder->id,
            'changed_by' => $mechanic->id,
            'old_status' => null,
            'new_status' => ServiceOrderStatus::WaitingQueue->value,
        ]);

        $invoice = $serviceOrder->invoice()->firstOrFail();

        $this->assertTrue($invoice->isDraft());
        $this->assertSame('INV-20261102-001', $invoice->invoice_number);
        $this->assertSame('75000.00', $invoice->total_amount);
        $this->assertSame('75000.00', $invoice->remaining_amount);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'service_type_id' => $serviceType->id,
            'description' => 'Servis ringan',
            'unit_price' => 75000,
            'line_total' => 75000,
        ]);
    }

    public function test_a_booking_cannot_be_checked_in_twice(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $booking = $this->confirmedBooking(User::factory()->create());
        $odometerIn = $booking->motorcycle->odometer_current;

        $this->actingAs($cashier)
            ->post('/staff/bookings/'.$booking->id.'/check-in', ['odometer_in' => $odometerIn])
            ->assertSessionHasNoErrors();

        $this->actingAs($cashier)
            ->post('/staff/bookings/'.$booking->id.'/check-in', ['odometer_in' => $odometerIn])
            ->assertForbidden();

        $this->assertSame(1, $booking->statusLogs()->count());
        $this->assertSame(1, ServiceOrder::query()->where('booking_id', $booking->id)->count());
        $this->assertSame(1, Invoice::query()->count());
    }

    public function test_staff_can_mark_a_booking_as_no_show_and_release_the_slot(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $booking = $this->confirmedBooking(User::factory()->create());
        $slot = $booking->slot;

        $this->actingAs($admin)
            ->patch('/staff/bookings/'.$booking->id.'/no-show', ['reason' => 'Tidak datang sampai slot berakhir.'])
            ->assertRedirect('/staff/bookings/'.$booking->id)
            ->assertSessionHasNoErrors();

        $booking->refresh();

        $this->assertSame(BookingStatus::NoShow, $booking->status);
        $this->assertSame(0, $slot->fresh()->booked_count);

        $this->assertDatabaseHas('booking_status_logs', [
            'booking_id' => $booking->id,
            'new_status' => BookingStatus::NoShow->value,
            'reason' => 'Tidak datang sampai slot berakhir.',
        ]);
    }

    public function test_staff_can_cancel_a_booking_with_a_reason(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $booking = $this->confirmedBooking(User::factory()->create());
        $slot = $booking->slot;

        $this->actingAs($cashier)
            ->patch('/staff/bookings/'.$booking->id.'/cancel', ['reason' => 'Pelanggan meminta jadwal lain.'])
            ->assertRedirect('/staff/bookings/'.$booking->id)
            ->assertSessionHasNoErrors();

        $booking->refresh();

        $this->assertSame(BookingStatus::Cancelled, $booking->status);
        $this->assertSame('Pelanggan meminta jadwal lain.', $booking->cancellation_reason);
        $this->assertNotNull($booking->cancelled_at);
        $this->assertSame(0, $slot->fresh()->booked_count);
    }

    public function test_search_filters_the_queue_by_customer_name(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $budi = $this->confirmedBooking(User::factory()->create(['name' => 'Budi Santoso']), ['queue_number' => 'A-001']);
        $siti = $this->confirmedBooking(User::factory()->create(['name' => 'Siti Rahma']), ['queue_number' => 'A-002'], 10);

        $this->actingAs($admin)
            ->get('/staff/bookings?date=2026-11-02&search=Budi')
            ->assertOk()
            ->assertSee($budi->queue_number)
            ->assertDontSee($siti->queue_number);
    }

    public function test_status_filter_only_shows_matching_bookings(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $confirmed = $this->confirmedBooking(User::factory()->create(), ['queue_number' => 'A-001']);
        $cancelled = $this->confirmedBooking(User::factory()->create(), ['queue_number' => 'A-002'], 10);
        $cancelled->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/staff/bookings?date=2026-11-02&status=cancelled')
            ->assertOk()
            ->assertSee($cancelled->queue_number)
            ->assertDontSee($confirmed->queue_number);
    }

    public function test_customer_cannot_access_staff_booking_routes(): void
    {
        $customer = User::factory()->create();
        $booking = $this->confirmedBooking($customer);

        $this->actingAs($customer)->get('/staff/bookings')->assertForbidden();
        $this->actingAs($customer)->get('/staff/bookings/'.$booking->id)->assertForbidden();
        $this->actingAs($customer)->post('/staff/bookings/'.$booking->id.'/check-in')->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function confirmedBooking(
        User $customer,
        array $attributes = [],
        int $hour = 9,
        string $date = '2026-11-02',
    ): Booking {
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $slot = BookingSlot::factory()
            ->startingAt($hour, $date)
            ->create(['booked_count' => 1]);

        return Booking::factory()->create([
            'user_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
            'booking_slot_id' => $slot->id,
            'scheduled_at' => $slot->scheduledAt(),
            'queue_date' => $date,
            'queue_number' => 'A-100',
            'status' => BookingStatus::Confirmed,
            'confirmed_at' => now(),
            ...$attributes,
        ]);
    }
}
