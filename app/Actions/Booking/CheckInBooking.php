<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckInBooking
{
    /**
     * Mark a confirmed booking as checked in.
     *
     * The service order itself is created in the workshop execution phase.
     *
     * @throws ValidationException
     */
    public function handle(Booking $booking, User $actor): Booking
    {
        return DB::transaction(function () use ($booking, $actor): Booking {
            $locked = Booking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === BookingStatus::CheckedIn) {
                throw ValidationException::withMessages([
                    'booking' => 'Booking ini sudah check-in.',
                ]);
            }

            if (! $locked->status->isOpen()) {
                throw ValidationException::withMessages([
                    'booking' => 'Hanya booking terkonfirmasi yang dapat check-in.',
                ]);
            }

            $locked->update([
                'status' => BookingStatus::CheckedIn,
                'checked_in_at' => now(),
            ]);

            $locked->statusLogs()->create([
                'changed_by' => $actor->getKey(),
                'old_status' => BookingStatus::Confirmed,
                'new_status' => BookingStatus::CheckedIn,
                'reason' => 'Check-in oleh staf.',
            ]);

            return $locked->load(['motorcycle', 'slot', 'services.serviceType', 'statusLogs.changedBy']);
        });
    }
}
