<?php

namespace App\Actions\ServiceOrder;

use App\Enums\ServiceOrderStatus;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionServiceOrderStatus
{
    /**
     * Move a work order to its next status and append the history row.
     *
     * Completion also records the odometer out and advances the motorcycle's
     * current odometer. The motorcycle row is locked so two work orders for
     * the same motorcycle cannot record conflicting readings.
     *
     * @throws ValidationException
     */
    public function handle(
        ServiceOrder $serviceOrder,
        ServiceOrderStatus $newStatus,
        User $actor,
        ?string $notes = null,
        ?int $odometerOut = null,
    ): ServiceOrder {
        return DB::transaction(function () use ($serviceOrder, $newStatus, $actor, $notes, $odometerOut): ServiceOrder {
            $locked = ServiceOrder::query()
                ->whereKey($serviceOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $locked->status;

            if (! $oldStatus->canTransitionTo($newStatus)) {
                throw ValidationException::withMessages([
                    'status' => 'Status tidak dapat diubah dari '.$oldStatus->label().' menjadi '.$newStatus->label().'.',
                ]);
            }

            $attributes = ['status' => $newStatus];

            if ($newStatus === ServiceOrderStatus::BeingInspected) {
                $attributes['started_at'] = $locked->started_at ?? now();
            }

            if ($newStatus === ServiceOrderStatus::Completed) {
                $attributes = [
                    ...$attributes,
                    ...$this->completionAttributes($locked, $odometerOut),
                ];
            }

            if ($newStatus === ServiceOrderStatus::PickedUp) {
                $attributes['picked_up_at'] = now();
            }

            $locked->update($attributes);

            $locked->statusLogs()->create([
                'changed_by' => $actor->getKey(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'notes' => $notes,
            ]);

            return $locked->load([
                'booking',
                'customer',
                'motorcycle',
                'activeMechanicAssignments.mechanic',
                'services.serviceType',
                'parts.sparepart',
                'statusLogs.changedBy',
            ]);
        });
    }

    /**
     * @return array{odometer_out: int, completed_at: Carbon}
     *
     * @throws ValidationException
     */
    private function completionAttributes(ServiceOrder $serviceOrder, ?int $odometerOut): array
    {
        if ($odometerOut === null) {
            throw ValidationException::withMessages([
                'odometer_out' => 'Odometer keluar wajib diisi saat menyelesaikan servis.',
            ]);
        }

        if ($odometerOut < 0) {
            throw ValidationException::withMessages([
                'odometer_out' => 'Odometer keluar tidak boleh negatif.',
            ]);
        }

        if ($odometerOut < $serviceOrder->odometer_in) {
            throw ValidationException::withMessages([
                'odometer_out' => 'Odometer keluar tidak boleh lebih kecil dari odometer masuk ('
                    .number_format($serviceOrder->odometer_in, 0, ',', '.').' km).',
            ]);
        }

        $motorcycle = Motorcycle::withTrashed()
            ->whereKey($serviceOrder->motorcycle_id)
            ->lockForUpdate()
            ->firstOrFail();

        $current = $motorcycle->odometer_current;

        if ($odometerOut < $current) {
            throw ValidationException::withMessages([
                'odometer_out' => 'Odometer keluar tidak boleh lebih kecil dari odometer terakhir ('
                    .number_format($current, 0, ',', '.').' km).',
            ]);
        }

        $motorcycle->update(['odometer_current' => $odometerOut]);

        return [
            'odometer_out' => $odometerOut,
            'completed_at' => now(),
        ];
    }
}
