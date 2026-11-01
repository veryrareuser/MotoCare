<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RescheduleBooking
{
    public function __construct(
        private AssertSlotIsBookable $assertSlotIsBookable,
        private NextQueueNumber $nextQueueNumber,
    ) {}

    /**
     * Move a booking to another slot, releasing the old place.
     *
     * Moving to a different date issues a new daily queue number for that
     * date; the booking number itself never changes.
     *
     * @throws ValidationException
     */
    public function handle(Booking $booking, BookingSlot $newSlot, User $actor): Booking
    {
        return DB::transaction(function () use ($booking, $newSlot, $actor): Booking {
            $locked = Booking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->status->isOpen()) {
                throw ValidationException::withMessages([
                    'booking_slot_id' => 'Booking yang sudah check-in atau selesai tidak dapat dijadwalkan ulang.',
                ]);
            }

            $slotIds = [$locked->booking_slot_id, $newSlot->getKey()];
            sort($slotIds);

            $lockedSlots = BookingSlot::query()
                ->whereIn('id', $slotIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $currentSlot = $lockedSlots->get($locked->booking_slot_id);
            $lockedNewSlot = $lockedSlots->get($newSlot->getKey());

            if (! $currentSlot instanceof BookingSlot || ! $lockedNewSlot instanceof BookingSlot) {
                throw ValidationException::withMessages([
                    'booking_slot_id' => 'Slot tidak ditemukan.',
                ]);
            }

            $this->assertSlotIsBookable->handle($lockedNewSlot, $currentSlot);

            $oldScheduledAt = $locked->scheduled_at;
            $newScheduledAt = $lockedNewSlot->scheduledAt();
            $newQueueDate = $lockedNewSlot->localStart()->startOfDay();

            $queueDate = $locked->queue_date->toDateString();
            $queueNumber = $locked->queue_number;

            if ($newQueueDate->toDateString() !== $queueDate) {
                $queue = $this->nextQueueNumber->handle($newQueueDate);
                $queueDate = $queue['date'];
                $queueNumber = $queue['number'];
            }

            $currentSlot->booked_count = max(0, $currentSlot->booked_count - 1);
            $currentSlot->save();

            $lockedNewSlot->increment('booked_count');

            $locked->update([
                'booking_slot_id' => $lockedNewSlot->getKey(),
                'scheduled_at' => $newScheduledAt,
                'queue_date' => $queueDate,
                'queue_number' => $queueNumber,
            ]);

            $locked->statusLogs()->create([
                'changed_by' => $actor->getKey(),
                'old_status' => BookingStatus::Confirmed,
                'new_status' => BookingStatus::Confirmed,
                'old_scheduled_at' => $oldScheduledAt,
                'new_scheduled_at' => $newScheduledAt,
                'reason' => 'Jadwal diubah.',
            ]);

            return $locked->load(['motorcycle', 'slot', 'services.serviceType', 'statusLogs.changedBy']);
        });
    }
}
