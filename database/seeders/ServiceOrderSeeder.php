<?php

namespace Database\Seeders;

use App\Actions\Booking\CheckInBooking;
use App\Actions\ServiceOrder\AddServiceOrderPart;
use App\Actions\ServiceOrder\AssignServiceOrderMechanic;
use App\Actions\ServiceOrder\TransitionServiceOrderStatus;
use App\Enums\BookingStatus;
use App\Enums\MechanicAssignmentRole;
use App\Enums\ServiceOrderStatus;
use App\Models\Booking;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
    /**
     * Turn the demo bookings into work orders so the workshop board is populated.
     */
    public function run(
        CheckInBooking $checkInBooking,
        AssignServiceOrderMechanic $assignServiceOrderMechanic,
        TransitionServiceOrderStatus $transitionServiceOrderStatus,
        AddServiceOrderPart $addServiceOrderPart,
    ): void {
        if (ServiceOrder::query()->exists()) {
            return;
        }

        $admin = User::query()->where('email', 'admin@motocare.test')->first();
        $mechanic = User::query()->where('email', 'mechanic@motocare.test')->first();

        if (! $admin instanceof User || ! $mechanic instanceof User) {
            return;
        }

        $bookings = Booking::query()
            ->where('status', BookingStatus::Confirmed)
            ->orderBy('scheduled_at')
            ->limit(2)
            ->get();

        $serviceOrders = $bookings->map(function (Booking $booking) use ($admin, $checkInBooking): ServiceOrder {
            return $checkInBooking->handle(
                $booking,
                $admin,
                $booking->motorcycle->odometer_current,
            );
        })->values();

        $first = $serviceOrders->get(0);

        if ($first instanceof ServiceOrder) {
            $assignServiceOrderMechanic->handle($first, $mechanic, MechanicAssignmentRole::Lead);
        }

        $second = $serviceOrders->get(1);

        if (! $second instanceof ServiceOrder) {
            return;
        }

        $assignServiceOrderMechanic->handle($second, $mechanic, MechanicAssignmentRole::Assistant);

        $transitionServiceOrderStatus->handle($second, ServiceOrderStatus::BeingInspected, $admin);
        $second = $transitionServiceOrderStatus->handle($second, ServiceOrderStatus::InProgress, $admin);

        $sparepart = Sparepart::query()
            ->active()
            ->where('stock_quantity', '>', 0)
            ->orderBy('id')
            ->first();

        if ($sparepart instanceof Sparepart) {
            $addServiceOrderPart->handle($second, $sparepart, 1, $admin);
        }
    }
}
