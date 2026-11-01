<?php

namespace Database\Seeders;

use App\Actions\Booking\CreateBooking;
use App\Actions\Booking\EnsureBookingSlots;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Motorcycle;
use App\Models\ServiceType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Seed a couple of demo bookings on the next operating day.
     */
    public function run(EnsureBookingSlots $ensureBookingSlots, CreateBooking $createBooking): void
    {
        $customer = User::query()->where('email', 'customer@motocare.test')->first();

        if (! $customer || Booking::query()->exists()) {
            return;
        }

        $primaryMotorcycle = $customer->motorcycles()->orderBy('id')->first();
        $secondaryMotorcycle = $customer->motorcycles()->orderBy('id')->skip(1)->first();
        $serviceTypes = ServiceType::query()->active()->orderBy('id')->take(2)->get();
        $primaryService = $serviceTypes->first();

        if (! $primaryMotorcycle instanceof Motorcycle || ! $primaryService instanceof ServiceType) {
            return;
        }

        $today = CarbonImmutable::now(config('app.display_timezone'))->startOfDay();

        $slots = $ensureBookingSlots
            ->handleRange($today, $today->addDays(7))
            ->filter(fn (BookingSlot $slot): bool => $slot->is_active && ! $slot->isFull() && ! $slot->isInThePast())
            ->values();

        $first = $slots->get(0);
        $second = $slots->get(1);

        if ($first instanceof BookingSlot) {
            $createBooking->handle(
                $customer,
                $primaryMotorcycle,
                $first,
                $serviceTypes->pluck('id')->all(),
                'Servis rutin dan pengecekan rem.',
            );
        }

        if ($second instanceof BookingSlot && $secondaryMotorcycle instanceof Motorcycle) {
            $createBooking->handle(
                $customer,
                $secondaryMotorcycle,
                $second,
                [$primaryService->getKey()],
                null,
            );
        }
    }
}
