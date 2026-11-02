<?php

namespace App\Actions\ServiceOrder;

use App\Actions\Billing\CreateInvoiceForServiceOrder;
use App\Enums\ServiceOrderStatus;
use App\Enums\UserRole;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateWalkInServiceOrder
{
    public function __construct(
        private NextWorkOrderNumber $nextWorkOrderNumber,
        private CreateInvoiceForServiceOrder $createInvoiceForServiceOrder,
    ) {}

    /**
     * Create a work order for a customer who arrived without a booking.
     *
     * @param  list<int|string>  $serviceTypeIds
     *
     * @throws ValidationException
     */
    public function handle(
        User $actor,
        Motorcycle $motorcycle,
        array $serviceTypeIds,
        int $odometerIn,
        ?string $notes = null,
    ): ServiceOrder {
        return DB::transaction(function () use ($actor, $motorcycle, $serviceTypeIds, $odometerIn, $notes): ServiceOrder {
            $lockedMotorcycle = Motorcycle::query()
                ->whereKey($motorcycle->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $customer = $lockedMotorcycle->customer;

            if (! $customer instanceof User
                || $customer->role !== UserRole::Customer
                || ! $customer->is_active) {
                throw ValidationException::withMessages([
                    'motorcycle_id' => 'Motor tidak terhubung dengan pelanggan aktif.',
                ]);
            }

            $this->assertOdometer($lockedMotorcycle, $odometerIn);

            $serviceTypes = ServiceType::query()
                ->active()
                ->whereIn('id', $serviceTypeIds)
                ->get();

            if ($serviceTypes->count() !== count(array_unique($serviceTypeIds))) {
                throw ValidationException::withMessages([
                    'service_type_ids' => 'Beberapa layanan yang dipilih sudah tidak tersedia.',
                ]);
            }

            if ($serviceTypes->isEmpty()) {
                throw ValidationException::withMessages([
                    'service_type_ids' => 'Pilih minimal satu layanan.',
                ]);
            }

            $checkedInAt = now();
            $number = $this->nextWorkOrderNumber->handle();

            $serviceOrder = ServiceOrder::query()->create([
                'work_order_number' => $number['number'],
                'booking_id' => null,
                'customer_id' => $customer->getKey(),
                'motorcycle_id' => $lockedMotorcycle->getKey(),
                'odometer_in' => $odometerIn,
                'status' => ServiceOrderStatus::WaitingQueue,
                'work_notes' => $notes,
                'checked_in_at' => $checkedInAt,
            ]);

            $serviceOrder->services()->createMany(
                $serviceTypes->map(fn (ServiceType $serviceType): array => [
                    'service_type_id' => $serviceType->getKey(),
                    'description' => $serviceType->name,
                    'quantity' => 1,
                    'unit_price_snapshot' => $serviceType->base_price,
                    'duration_snapshot_minutes' => $serviceType->estimated_duration_minutes,
                ])->all(),
            );

            $serviceOrder->statusLogs()->create([
                'changed_by' => $actor->getKey(),
                'old_status' => null,
                'new_status' => ServiceOrderStatus::WaitingQueue,
                'notes' => 'Work order walk-in dibuat.',
            ]);

            $this->createInvoiceForServiceOrder->handle($serviceOrder);

            $lockedMotorcycle->update(['odometer_current' => $odometerIn]);

            return $serviceOrder->load([
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
