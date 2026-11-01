<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarkBookingNoShow
{
    /**
     * Mark a confirmed booking as a no-show and release its slot.
     *
     * @throws ValidationException
     */
    public function handle(Booking $booking, User $actor, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking, $actor, $reason): Booking {
            $locked = Booking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->status->isOpen()) {
                throw ValidationException::withMessages([
                    'reason' => 'Hanya booking terkonfirmasi yang dapat ditandai tidak hadir.',
                ]);
            }

            $slot = BookingSlot::query()
                ->whereKey($locked->booking_slot_id)
                ->lockForUpdate()
                ->firstOrFail();

            $slot->booked_count = max(0, $slot->booked_count - 1);
            $slot->save();

            $locked->update(['status' => BookingStatus::NoShow]);

            $locked->statusLogs()->create([
                'changed_by' => $actor->getKey(),
                'old_status' => BookingStatus::Confirmed,
                'new_status' => BookingStatus::NoShow,
                'reason' => $reason ?? 'Pelanggan tidak hadir.',
            ]);

            return $locked->load(['motorcycle', 'slot', 'services.serviceType', 'statusLogs.changedBy']);
        });
    }
}
