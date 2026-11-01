<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Motorcycle;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBooking
{
    public function __construct(
        private AssertSlotIsBookable $assertSlotIsBookable,
        private NextQueueNumber $nextQueueNumber,
    ) {}

    /**
     * Confirm a booking for the customer and issue its daily queue number.
     *
     * The slot row is locked before capacity is read so two simultaneous
     * requests cannot both claim the last remaining place.
     *
     * @param  list<int|string>  $serviceTypeIds
     *
     * @throws ValidationException
     */
    public function handle(
        User $customer,
        Motorcycle $motorcycle,
        BookingSlot $slot,
        array $serviceTypeIds,
        ?string $customerNote = null,
    ): Booking {
        if ($motorcycle->user_id !== $customer->getKey()) {
            throw ValidationException::withMessages([
                'motorcycle_id' => 'Motor ini bukan milik akun Anda.',
            ]);
        }

        $serviceTypeIds = array_values(array_unique(array_map('intval', $serviceTypeIds)));

        if ($serviceTypeIds === []) {
            throw ValidationException::withMessages([
                'service_type_ids' => 'Pilih minimal satu layanan.',
            ]);
        }

        return DB::transaction(function () use ($customer, $motorcycle, $slot, $serviceTypeIds, $customerNote): Booking {
            $lockedSlot = BookingSlot::query()
                ->whereKey($slot->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertSlotIsBookable->handle($lockedSlot);

            $serviceTypes = ServiceType::query()
                ->active()
                ->whereIn('id', $serviceTypeIds)
                ->get();

            if ($serviceTypes->count() !== count($serviceTypeIds)) {
                throw ValidationException::withMessages([
                    'service_type_ids' => 'Beberapa layanan yang dipilih sudah tidak tersedia.',
                ]);
            }

            $queueDate = $lockedSlot->localStart()->startOfDay();
            $queue = $this->nextQueueNumber->handle($queueDate);
            $scheduledAt = $lockedSlot->scheduledAt();

            $lockedSlot->increment('booked_count');

            $booking = Booking::query()->create([
                'booking_number' => 'BK-'.$queueDate->format('Ymd').'-'.str_pad((string) $queue['sequence'], 3, '0', STR_PAD_LEFT),
                'user_id' => $customer->getKey(),
                'motorcycle_id' => $motorcycle->getKey(),
                'booking_slot_id' => $lockedSlot->getKey(),
                'scheduled_at' => $scheduledAt,
                'queue_date' => $queue['date'],
                'queue_number' => $queue['number'],
                'status' => BookingStatus::Confirmed,
                'customer_note' => $customerNote,
                'confirmed_at' => now(),
            ]);

            $booking->services()->createMany(
                $serviceTypes->map(fn (ServiceType $serviceType): array => [
                    'service_type_id' => $serviceType->getKey(),
                    'price_snapshot' => $serviceType->base_price,
                    'duration_snapshot_minutes' => $serviceType->estimated_duration_minutes,
                ])->all(),
            );

            $booking->statusLogs()->create([
                'changed_by' => $customer->getKey(),
                'old_status' => null,
                'new_status' => BookingStatus::Confirmed,
                'new_scheduled_at' => $scheduledAt,
                'reason' => 'Booking dibuat.',
            ]);

            return $booking->load(['motorcycle', 'slot', 'services.serviceType', 'statusLogs.changedBy']);
        });
    }
}
