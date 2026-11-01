<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Motorcycle;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WorkshopSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
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

    public function test_guests_are_redirected_from_bookings(): void
    {
        $this->get('/bookings')->assertRedirect('/login');
        $this->get('/bookings/create')->assertRedirect('/login');
    }

    public function test_customer_can_create_a_confirmed_booking(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create([
            'base_price' => 75000,
            'estimated_duration_minutes' => 60,
        ]);
        $slot = $this->slotAt(9);

        $response = $this->actingAs($customer)->post('/bookings', [
            'motorcycle_id' => $motorcycle->id,
            'booking_slot_id' => $slot->id,
            'service_type_ids' => [$serviceType->id],
            'customer_note' => 'Rem depan berdecit.',
        ]);

        $booking = Booking::query()->firstOrFail();

        $response
            ->assertRedirect('/bookings/'.$booking->id)
            ->assertSessionHasNoErrors();

        $this->assertSame($customer->id, $booking->user_id);
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame('A-001', $booking->queue_number);
        $this->assertSame('BK-20261102-001', $booking->booking_number);
        $this->assertSame('2026-11-02', $booking->queue_date->toDateString());
        $this->assertSame('2026-11-02 02:00:00', $booking->scheduled_at->utc()->format('Y-m-d H:i:s'));
        $this->assertNotNull($booking->confirmed_at);
        $this->assertSame('Rem depan berdecit.', $booking->customer_note);

        $this->assertSame(1, $slot->fresh()->booked_count);

        $service = $booking->services()->firstOrFail();
        $this->assertSame('75000.00', $service->price_snapshot);
        $this->assertSame(60, $service->duration_snapshot_minutes);

        $this->assertDatabaseHas('booking_status_logs', [
            'booking_id' => $booking->id,
            'changed_by' => $customer->id,
            'old_status' => null,
            'new_status' => BookingStatus::Confirmed->value,
        ]);
    }

    public function test_service_price_changes_do_not_alter_existing_booking_snapshots(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create([
            'base_price' => 75000,
            'estimated_duration_minutes' => 45,
        ]);
        $slot = $this->slotAt(9);

        $this->actingAs($customer)->post('/bookings', [
            'motorcycle_id' => $motorcycle->id,
            'booking_slot_id' => $slot->id,
            'service_type_ids' => [$serviceType->id],
        ])->assertSessionHasNoErrors();

        $booking = Booking::query()->firstOrFail();

        $serviceType->update([
            'base_price' => 150000,
            'estimated_duration_minutes' => 90,
        ]);

        $snapshot = $booking->services()->firstOrFail();

        $this->assertSame('75000.00', $snapshot->price_snapshot);
        $this->assertSame(45, $snapshot->duration_snapshot_minutes);
    }

    public function test_a_full_slot_is_rejected(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create();
        $slot = $this->slotAt(9, ['capacity' => 2, 'booked_count' => 2]);

        $this->actingAs($customer)
            ->from('/bookings/create')
            ->post('/bookings', [
                'motorcycle_id' => $motorcycle->id,
                'booking_slot_id' => $slot->id,
                'service_type_ids' => [$serviceType->id],
            ])
            ->assertRedirect('/bookings/create')
            ->assertSessionHasErrors('booking_slot_id');

        $this->assertDatabaseCount('bookings', 0);
        $this->assertSame(2, $slot->fresh()->booked_count);
    }

    public function test_duplicate_services_are_rejected(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create();
        $slot = $this->slotAt(9);

        $this->actingAs($customer)
            ->from('/bookings/create')
            ->post('/bookings', [
                'motorcycle_id' => $motorcycle->id,
                'booking_slot_id' => $slot->id,
                'service_type_ids' => [$serviceType->id, $serviceType->id],
            ])
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_queue_numbers_increment_and_reset_per_day(): void
    {
        $customer = User::factory()->create();
        $firstMotorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $secondMotorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create();
        $morning = $this->slotAt(9);
        $midday = $this->slotAt(11);
        $nextDay = $this->slotAt(9, ['slot_date' => '2026-11-03']);

        $this->actingAs($customer)->post('/bookings', [
            'motorcycle_id' => $firstMotorcycle->id,
            'booking_slot_id' => $morning->id,
            'service_type_ids' => [$serviceType->id],
        ])->assertSessionHasNoErrors();

        $this->actingAs($customer)->post('/bookings', [
            'motorcycle_id' => $secondMotorcycle->id,
            'booking_slot_id' => $midday->id,
            'service_type_ids' => [$serviceType->id],
        ])->assertSessionHasNoErrors();

        $this->actingAs($customer)->post('/bookings', [
            'motorcycle_id' => $firstMotorcycle->id,
            'booking_slot_id' => $nextDay->id,
            'service_type_ids' => [$serviceType->id],
        ])->assertSessionHasNoErrors();

        $this->assertSame(
            ['A-001', 'A-002', 'A-001'],
            Booking::query()->orderBy('id')->pluck('queue_number')->all(),
        );
        $this->assertSame(
            ['BK-20261102-001', 'BK-20261102-002', 'BK-20261103-001'],
            Booking::query()->orderBy('id')->pluck('booking_number')->all(),
        );
    }

    public function test_a_slot_outside_the_booking_window_is_rejected(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create();
        $slot = $this->slotAt(9, ['slot_date' => '2026-11-17']);

        $this->actingAs($customer)
            ->from('/bookings/create')
            ->post('/bookings', [
                'motorcycle_id' => $motorcycle->id,
                'booking_slot_id' => $slot->id,
                'service_type_ids' => [$serviceType->id],
            ])
            ->assertSessionHasErrors('booking_slot_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_a_past_slot_is_rejected(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create();
        $slot = $this->slotAt(6);

        $this->actingAs($customer)
            ->from('/bookings/create')
            ->post('/bookings', [
                'motorcycle_id' => $motorcycle->id,
                'booking_slot_id' => $slot->id,
                'service_type_ids' => [$serviceType->id],
            ])
            ->assertSessionHasErrors('booking_slot_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_an_inactive_slot_is_rejected(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create();
        $slot = $this->slotAt(9, ['is_active' => false]);

        $this->actingAs($customer)
            ->from('/bookings/create')
            ->post('/bookings', [
                'motorcycle_id' => $motorcycle->id,
                'booking_slot_id' => $slot->id,
                'service_type_ids' => [$serviceType->id],
            ])
            ->assertSessionHasErrors('booking_slot_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_customer_cannot_book_another_customers_motorcycle(): void
    {
        $customer = User::factory()->create();
        $otherMotorcycle = Motorcycle::factory()->create();
        $serviceType = ServiceType::factory()->create();
        $slot = $this->slotAt(9);

        $this->actingAs($customer)
            ->from('/bookings/create')
            ->post('/bookings', [
                'motorcycle_id' => $otherMotorcycle->id,
                'booking_slot_id' => $slot->id,
                'service_type_ids' => [$serviceType->id],
            ])
            ->assertSessionHasErrors('motorcycle_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_an_inactive_service_cannot_be_booked(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create(['is_active' => false]);
        $slot = $this->slotAt(9);

        $this->actingAs($customer)
            ->from('/bookings/create')
            ->post('/bookings', [
                'motorcycle_id' => $motorcycle->id,
                'booking_slot_id' => $slot->id,
                'service_type_ids' => [$serviceType->id],
            ])
            ->assertSessionHasErrors('service_type_ids.0');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_rescheduling_within_the_same_day_keeps_the_queue_number(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create();
        $originalSlot = $this->slotAt(9);
        $newSlot = $this->slotAt(11);

        $this->actingAs($customer)->post('/bookings', [
            'motorcycle_id' => $motorcycle->id,
            'booking_slot_id' => $originalSlot->id,
            'service_type_ids' => [$serviceType->id],
        ])->assertSessionHasNoErrors();

        $booking = Booking::query()->firstOrFail();
        $bookingNumber = $booking->booking_number;

        $this->actingAs($customer)
            ->patch('/bookings/'.$booking->id.'/reschedule', ['booking_slot_id' => $newSlot->id])
            ->assertRedirect('/bookings/'.$booking->id)
            ->assertSessionHasNoErrors();

        $booking->refresh();

        $this->assertSame($newSlot->id, $booking->booking_slot_id);
        $this->assertSame('A-001', $booking->queue_number);
        $this->assertSame($bookingNumber, $booking->booking_number);
        $this->assertSame('2026-11-02 04:00:00', $booking->scheduled_at->utc()->format('Y-m-d H:i:s'));

        $this->assertSame(0, $originalSlot->fresh()->booked_count);
        $this->assertSame(1, $newSlot->fresh()->booked_count);

        $log = $booking->statusLogs()->latest('id')->firstOrFail();
        $this->assertNotNull($log->old_scheduled_at);
        $this->assertNotNull($log->new_scheduled_at);
        $this->assertSame($customer->id, $log->changed_by);
    }

    public function test_rescheduling_to_another_day_issues_a_new_queue_number(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create();
        $originalSlot = $this->slotAt(9);
        $nextDaySlot = $this->slotAt(9, ['slot_date' => '2026-11-03']);

        $this->actingAs($customer)->post('/bookings', [
            'motorcycle_id' => $motorcycle->id,
            'booking_slot_id' => $originalSlot->id,
            'service_type_ids' => [$serviceType->id],
        ])->assertSessionHasNoErrors();

        $booking = Booking::query()->firstOrFail();
        $bookingNumber = $booking->booking_number;

        $this->actingAs($customer)
            ->patch('/bookings/'.$booking->id.'/reschedule', ['booking_slot_id' => $nextDaySlot->id])
            ->assertSessionHasNoErrors();

        $booking->refresh();

        $this->assertSame('2026-11-03', $booking->queue_date->toDateString());
        $this->assertSame('A-001', $booking->queue_number);
        $this->assertSame($bookingNumber, $booking->booking_number);
        $this->assertSame(0, $originalSlot->fresh()->booked_count);
        $this->assertSame(1, $nextDaySlot->fresh()->booked_count);
    }

    public function test_rescheduling_to_a_full_slot_is_rejected(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $serviceType = ServiceType::factory()->create();
        $originalSlot = $this->slotAt(9);
        $fullSlot = $this->slotAt(11, ['capacity' => 2, 'booked_count' => 2]);

        $this->actingAs($customer)->post('/bookings', [
            'motorcycle_id' => $motorcycle->id,
            'booking_slot_id' => $originalSlot->id,
            'service_type_ids' => [$serviceType->id],
        ])->assertSessionHasNoErrors();

        $booking = Booking::query()->firstOrFail();

        $this->actingAs($customer)
            ->from('/bookings/'.$booking->id)
            ->patch('/bookings/'.$booking->id.'/reschedule', ['booking_slot_id' => $fullSlot->id])
            ->assertSessionHasErrors('booking_slot_id');

        $this->assertSame($originalSlot->id, $booking->fresh()->booking_slot_id);
        $this->assertSame(1, $originalSlot->fresh()->booked_count);
        $this->assertSame(2, $fullSlot->fresh()->booked_count);
    }

    public function test_a_checked_in_booking_cannot_be_rescheduled(): void
    {
        $customer = User::factory()->create();
        $booking = $this->bookingFor($customer);
        $newSlot = $this->slotAt(11);

        $booking->update([
            'status' => BookingStatus::CheckedIn,
            'checked_in_at' => now(),
        ]);

        $this->actingAs($customer)
            ->patch('/bookings/'.$booking->id.'/reschedule', ['booking_slot_id' => $newSlot->id])
            ->assertForbidden();

        $this->assertSame($booking->booking_slot_id, $booking->fresh()->booking_slot_id);
    }

    public function test_customer_can_cancel_before_check_in(): void
    {
        $customer = User::factory()->create();
        $booking = $this->bookingFor($customer);
        $slot = $booking->slot;

        $this->actingAs($customer)
            ->patch('/bookings/'.$booking->id.'/cancel', ['reason' => 'Ada keperluan mendadak.'])
            ->assertRedirect('/bookings/'.$booking->id)
            ->assertSessionHasNoErrors();

        $booking->refresh();

        $this->assertSame(BookingStatus::Cancelled, $booking->status);
        $this->assertNotNull($booking->cancelled_at);
        $this->assertSame('Ada keperluan mendadak.', $booking->cancellation_reason);
        $this->assertSame(0, $slot->fresh()->booked_count);

        $this->assertDatabaseHas('booking_status_logs', [
            'booking_id' => $booking->id,
            'changed_by' => $customer->id,
            'new_status' => BookingStatus::Cancelled->value,
        ]);
    }

    public function test_a_checked_in_booking_cannot_be_cancelled_by_the_customer(): void
    {
        $customer = User::factory()->create();
        $booking = $this->bookingFor($customer);

        $booking->update([
            'status' => BookingStatus::CheckedIn,
            'checked_in_at' => now(),
        ]);

        $this->actingAs($customer)
            ->patch('/bookings/'.$booking->id.'/cancel')
            ->assertForbidden();

        $this->assertSame(BookingStatus::CheckedIn, $booking->fresh()->status);
        $this->assertSame(1, $booking->slot->fresh()->booked_count);
    }

    public function test_customer_can_view_their_booking_detail_and_reschedule_options(): void
    {
        $customer = User::factory()->create();
        $booking = $this->bookingFor($customer);

        WorkshopSchedule::factory()->create(['day_of_week' => 1]);

        $this->actingAs($customer)
            ->get('/bookings/'.$booking->id)
            ->assertOk()
            ->assertSee($booking->queue_number)
            ->assertSee($booking->booking_number)
            ->assertSee('Riwayat booking')
            ->assertSee('Jadwalkan ulang');
    }

    public function test_customer_only_sees_their_own_bookings(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();

        $own = $this->bookingFor($customer, ['queue_number' => 'A-778']);
        $this->bookingFor($other, ['queue_number' => 'A-777'], 10);

        $this->actingAs($customer)
            ->get('/bookings')
            ->assertOk()
            ->assertSee($own->queue_number)
            ->assertDontSee('A-777');
    }

    public function test_customer_cannot_view_or_change_another_customers_booking(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $booking = $this->bookingFor($other);
        $newSlot = $this->slotAt(11);

        $this->actingAs($customer)
            ->get('/bookings/'.$booking->id)
            ->assertForbidden();

        $this->actingAs($customer)
            ->patch('/bookings/'.$booking->id.'/reschedule', ['booking_slot_id' => $newSlot->id])
            ->assertForbidden();

        $this->actingAs($customer)
            ->patch('/bookings/'.$booking->id.'/cancel')
            ->assertForbidden();

        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    public function test_staff_cannot_access_customer_booking_routes(): void
    {
        $staff = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($staff)->get('/bookings')->assertForbidden();
        $this->actingAs($staff)->get('/bookings/create')->assertForbidden();
    }

    public function test_booking_form_lists_active_services_and_open_slots(): void
    {
        $customer = User::factory()->create();
        Motorcycle::factory()->create(['user_id' => $customer->id]);
        ServiceType::factory()->create(['name' => 'Servis CVT', 'is_active' => true]);

        WorkshopSchedule::factory()->create([
            'day_of_week' => 1,
            'open_time' => '08:00:00',
            'close_time' => '17:00:00',
            'slot_duration_minutes' => 60,
            'capacity_per_slot' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->get('/bookings/create')
            ->assertOk()
            ->assertSee('Servis CVT')
            ->assertSee('08:00 - 09:00')
            ->assertSee('Sisa 2 slot');
    }

    /**
     * Create a booking for the given customer on the frozen Monday.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function bookingFor(User $customer, array $attributes = [], int $hour = 9): Booking
    {
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $slot = $this->slotAt($hour, ['booked_count' => 1]);

        return Booking::factory()->create([
            'user_id' => $customer->id,
            'motorcycle_id' => $motorcycle->id,
            'booking_slot_id' => $slot->id,
            'scheduled_at' => $slot->scheduledAt(),
            'queue_date' => '2026-11-02',
            'queue_number' => 'A-100',
            'status' => BookingStatus::Confirmed,
            'confirmed_at' => now(),
            ...$attributes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function slotAt(int $hour, array $attributes = []): BookingSlot
    {
        return BookingSlot::factory()
            ->startingAt($hour, '2026-11-02')
            ->create($attributes);
    }
}
