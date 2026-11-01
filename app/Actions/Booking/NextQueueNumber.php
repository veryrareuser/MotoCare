<?php

namespace App\Actions\Booking;

use App\Models\DailySequence;
use Carbon\CarbonImmutable;

class NextQueueNumber
{
    /**
     * Issue the next daily queue number for the given date.
     *
     * The sequence row is locked so concurrent confirmations cannot receive
     * the same number. The number is unique per queue date by construction
     * and by the unique index on bookings.
     *
     * @return array{date: string, number: string, sequence: int}
     */
    public function handle(CarbonImmutable $queueDate): array
    {
        $date = $queueDate->toDateString();
        $timestamp = now();

        DailySequence::query()->insertOrIgnore([
            'name' => 'queue',
            'date' => $date,
            'last_number' => 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $sequence = DailySequence::query()
            ->where('name', 'queue')
            ->where('date', $date)
            ->lockForUpdate()
            ->firstOrFail();

        $next = $sequence->last_number + 1;
        $sequence->update(['last_number' => $next]);

        return [
            'date' => $date,
            'number' => config('booking.queue_prefix').'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT),
            'sequence' => $next,
        ];
    }
}
