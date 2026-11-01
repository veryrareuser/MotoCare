<?php

namespace Tests\Feature;

use App\Actions\Booking\EnsureBookingSlots;
use App\Models\BookingSlot;
use App\Models\WorkshopSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSlotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-11-02 07:00:00', config('app.display_timezone')));
    }

    public function test_slots_are_materialized_from_the_active_schedule(): void
    {
        WorkshopSchedule::factory()->create([
            'day_of_week' => 1,
            'open_time' => '08:00:00',
            'close_time' => '17:00:00',
            'slot_duration_minutes' => 60,
            'capacity_per_slot' => 2,
            'is_active' => true,
        ]);

        $slots = app(EnsureBookingSlots::class)->handle(CarbonImmutable::parse('2026-11-02'));

        $this->assertCount(9, $slots);
        $this->assertSame('08:00:00', $slots->first()->start_time);
        $this->assertSame('16:00:00', $slots->last()->start_time);
        $this->assertSame('17:00:00', $slots->last()->end_time);
        $this->assertSame(2, $slots->first()->capacity);
        $this->assertSame(0, $slots->first()->booked_count);
    }

    public function test_slot_start_is_stored_as_utc(): void
    {
        $slot = BookingSlot::factory()
            ->startingAt(8, '2026-11-02')
            ->create();

        $this->assertSame('2026-11-02 01:00:00', $slot->scheduledAt()->format('Y-m-d H:i:s'));
        $this->assertSame('08:00 - 09:00', $slot->displayRange());
        $this->assertSame('Senin, 02 Nov 2026', $slot->displayDate());
    }

    public function test_slot_generation_is_idempotent_and_preserves_bookings(): void
    {
        WorkshopSchedule::factory()->create(['day_of_week' => 1]);

        $ensureBookingSlots = app(EnsureBookingSlots::class);
        $ensureBookingSlots->handle(CarbonImmutable::parse('2026-11-02'));

        BookingSlot::query()->where('start_time', '08:00:00')->update(['booked_count' => 1]);

        $ensureBookingSlots->handle(CarbonImmutable::parse('2026-11-02'));

        $this->assertSame(9, BookingSlot::query()->count());
        $this->assertSame(1, BookingSlot::query()->where('start_time', '08:00:00')->value('booked_count'));
    }

    public function test_closed_days_produce_no_slots(): void
    {
        WorkshopSchedule::factory()->inactive()->create(['day_of_week' => 7]);

        $slots = app(EnsureBookingSlots::class)->handle(CarbonImmutable::parse('2026-11-01'));

        $this->assertCount(0, $slots);
        $this->assertSame(0, BookingSlot::query()->count());
    }

    public function test_a_range_only_creates_slots_for_operating_days(): void
    {
        WorkshopSchedule::factory()->create(['day_of_week' => 1]);
        WorkshopSchedule::factory()->create(['day_of_week' => 3]);
        WorkshopSchedule::factory()->inactive()->create(['day_of_week' => 2]);

        $slots = app(EnsureBookingSlots::class)->handleRange(
            CarbonImmutable::parse('2026-11-02'),
            CarbonImmutable::parse('2026-11-04'),
        );

        $dates = $slots->pluck('slot_date')->map->toDateString()->unique()->values()->all();

        $this->assertSame(['2026-11-02', '2026-11-04'], $dates);
        $this->assertSame(18, $slots->count());
    }

    public function test_remaining_capacity_and_full_state(): void
    {
        $slot = BookingSlot::factory()->create(['capacity' => 3, 'booked_count' => 2]);

        $this->assertSame(1, $slot->remainingCapacity());
        $this->assertFalse($slot->isFull());

        $slot->update(['booked_count' => 3]);

        $this->assertSame(0, $slot->fresh()->remainingCapacity());
        $this->assertTrue($slot->fresh()->isFull());
    }
}
