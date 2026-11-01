<?php

namespace App\Actions\Booking;

use App\Models\BookingSlot;
use App\Models\WorkshopSchedule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class EnsureBookingSlots
{
    /**
     * Materialize the bookable slots for a date from the active schedule.
     *
     * Slots are inserted idempotently, so capacity and booked counts of
     * existing slots are never overwritten by a later schedule change.
     *
     * @return Collection<int, BookingSlot>
     */
    public function handle(CarbonImmutable $date): Collection
    {
        return $this->handleRange($date, $date);
    }

    /**
     * Materialize every operating slot in an inclusive date range.
     *
     * @return Collection<int, BookingSlot>
     */
    public function handleRange(CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        $schedules = WorkshopSchedule::query()
            ->active()
            ->get()
            ->keyBy('day_of_week');

        $timestamp = now();
        $rows = [];

        for ($date = $start->startOfDay(); $date->lessThanOrEqualTo($end); $date = $date->addDay()) {
            $schedule = $schedules->get($date->dayOfWeekIso);

            if ($schedule === null) {
                continue;
            }

            $rows = [...$rows, ...$this->buildRows($date, $schedule, $timestamp)];
        }

        if ($rows !== []) {
            BookingSlot::query()->insertOrIgnore($rows);
        }

        return BookingSlot::query()
            ->whereBetween('slot_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Build one row per slot inside the operating window.
     *
     * @return list<array<string, mixed>>
     */
    private function buildRows(CarbonImmutable $date, WorkshopSchedule $schedule, CarbonInterface $timestamp): array
    {
        $day = $date->toDateString();
        $timezone = config('app.display_timezone');
        $cursor = CarbonImmutable::parse($day.' '.$schedule->open_time, $timezone);
        $close = CarbonImmutable::parse($day.' '.$schedule->close_time, $timezone);
        $duration = max(1, $schedule->slot_duration_minutes);

        $rows = [];

        while ($cursor->addMinutes($duration)->lessThanOrEqualTo($close)) {
            $rows[] = [
                'slot_date' => $day,
                'start_time' => $cursor->format('H:i:s'),
                'end_time' => $cursor->addMinutes($duration)->format('H:i:s'),
                'capacity' => $schedule->capacity_per_slot,
                'booked_count' => 0,
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            $cursor = $cursor->addMinutes($duration);
        }

        return $rows;
    }
}
