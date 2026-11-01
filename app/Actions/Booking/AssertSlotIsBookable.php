<?php

namespace App\Actions\Booking;

use App\Models\BookingSlot;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class AssertSlotIsBookable
{
    /**
     * Validate that a locked slot can still receive a booking.
     *
     * @throws ValidationException
     */
    public function handle(BookingSlot $slot, ?BookingSlot $currentSlot = null): void
    {
        if (! $slot->is_active) {
            $this->fail('Slot ini sedang tidak dibuka untuk booking.');
        }

        if ($currentSlot !== null && $slot->is($currentSlot)) {
            $this->fail('Pilih slot yang berbeda dari jadwal saat ini.');
        }

        if ($slot->isInThePast()) {
            $this->fail('Slot ini sudah lewat. Pilih waktu lain.');
        }

        $today = CarbonImmutable::now(config('app.display_timezone'))->startOfDay();
        $lastBookableDay = $today
            ->addDays((int) config('booking.booking_window_days'))
            ->endOfDay();

        if ($slot->localStart()->greaterThan($lastBookableDay)) {
            $this->fail('Booking hanya dibuka sampai '.(int) config('booking.booking_window_days').' hari ke depan.');
        }

        if ($slot->isFull()) {
            $this->fail('Slot ini sudah penuh. Silakan pilih slot lain.');
        }
    }

    /**
     * @throws ValidationException
     */
    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'booking_slot_id' => $message,
        ]);
    }
}
