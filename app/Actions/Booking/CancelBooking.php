<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelBooking
{
    /**
     * Cancel a booking that has not been checked in yet and free its slot.
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
                    'reason' => 'Booking hanya dapat dibatalkan sebelum check-in.',
                ]);
            }

            $slot = BookingSlot::query()
                ->whereKey($locked->booking_slot_id)
                ->lockForUpdate()
                ->firstOrFail();

            $slot->booked_count = max(0, $slot->booked_count - 1);
            $slot->save();

            $locked->update([
                'status' => BookingStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            $locked->statusLogs()->create([
                'changed_by' => $actor->getKey(),
                'old_status' => BookingStatus::Confirmed,
                'new_status' => BookingStatus::Cancelled,
                'reason' => $reason ?? 'Booking dibatalkan.',
            ]);

            return $locked->load(['motorcycle', 'slot', 'services.serviceType', 'statusLogs.changedBy']);
        });
    }
}
