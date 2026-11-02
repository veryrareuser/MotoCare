<?php

namespace App\Actions\Booking;

use App\Actions\Billing\CreateInvoiceForServiceOrder;
use App\Actions\ServiceOrder\NextWorkOrderNumber;
use App\Enums\BookingStatus;
use App\Enums\ServiceOrderStatus;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckInBooking
{
    public function __construct(
        private NextWorkOrderNumber $nextWorkOrderNumber,
        private CreateInvoiceForServiceOrder $createInvoiceForServiceOrder,
    ) {}

    /**
     * Check a booking in, create its work order, and snapshot its services.
     *
     * The booking row is locked before the status is checked, so a second
     * request cannot create a duplicate work order for the same booking.
     *
     * @throws ValidationException
     */
    public function handle(Booking $booking, User $actor, int $odometerIn): ServiceOrder
    {
        return DB::transaction(function () use ($booking, $actor, $odometerIn): ServiceOrder {
            $locked = Booking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== BookingStatus::Confirmed) {
                throw ValidationException::withMessages([
                    'booking' => 'Hanya booking terkonfirmasi yang dapat check-in.',
                ]);
            }

            if (ServiceOrder::query()->where('booking_id', $locked->getKey())->exists()) {
                throw ValidationException::withMessages([
                    'booking' => 'Booking ini sudah memiliki work order.',
                ]);
            }

            $motorcycle = Motorcycle::withTrashed()
                ->whereKey($locked->motorcycle_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertOdometer($motorcycle, $odometerIn);

            $checkedInAt = now();
            $number = $this->nextWorkOrderNumber->handle();

            $serviceOrder = ServiceOrder::query()->create([
                'work_order_number' => $number['number'],
                'booking_id' => $locked->getKey(),
                'customer_id' => $locked->user_id,
                'motorcycle_id' => $locked->motorcycle_id,
                'odometer_in' => $odometerIn,
                'status' => ServiceOrderStatus::WaitingQueue,
                'work_notes' => $locked->customer_note,
                'checked_in_at' => $checkedInAt,
            ]);

            $bookingServices = $locked->services()->with('serviceType')->get();

            $serviceOrder->services()->createMany(
                $bookingServices->map(fn (BookingService $service): array => [
                    'service_type_id' => $service->service_type_id,
                    'description' => $service->serviceType->name,
                    'quantity' => 1,
                    'unit_price_snapshot' => $service->price_snapshot,
                    'duration_snapshot_minutes' => $service->duration_snapshot_minutes,
                ])->all(),
            );

            $serviceOrder->statusLogs()->create([
                'changed_by' => $actor->getKey(),
                'old_status' => null,
                'new_status' => ServiceOrderStatus::WaitingQueue,
                'notes' => 'Work order dibuat dari booking '.$locked->booking_number.'.',
            ]);

            $locked->update([
                'status' => BookingStatus::ConvertedToService,
                'checked_in_at' => $checkedInAt,
            ]);

            $locked->statusLogs()->create([
                'changed_by' => $actor->getKey(),
                'old_status' => BookingStatus::Confirmed,
                'new_status' => BookingStatus::ConvertedToService,
                'reason' => 'Check-in oleh staf. Work order '.$number['number'].' dibuat.',
            ]);

            $this->createInvoiceForServiceOrder->handle($serviceOrder);

            $motorcycle->update(['odometer_current' => $odometerIn]);

            return $serviceOrder->load([
                'booking',
                'customer',
                'motorcycle',
                'services.serviceType',
                'statusLogs.changedBy',
                'invoice.items',
            ]);
        });
    }

    /**
     * @throws ValidationException
     */
    private function assertOdometer(Motorcycle $motorcycle, int $odometerIn): void
    {
        if ($odometerIn < 0) {
            throw ValidationException::withMessages([
                'odometer_in' => 'Odometer masuk tidak boleh negatif.',
            ]);
        }

        $current = $motorcycle->odometer_current;

        if ($odometerIn < $current) {
            throw ValidationException::withMessages([
                'odometer_in' => 'Odometer masuk tidak boleh lebih kecil dari odometer terakhir ('
                    .number_format($current, 0, ',', '.').' km).',
            ]);
        }
    }
}
