<?php

namespace App\Actions\ServiceOrder;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderMechanic;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UnassignServiceOrderMechanic
{
    /**
     * Release a mechanic from a work order while keeping the assignment row.
     *
     * @throws ValidationException
     */
    public function handle(ServiceOrder $serviceOrder, ServiceOrderMechanic $assignment): ServiceOrderMechanic
    {
        return DB::transaction(function () use ($serviceOrder, $assignment): ServiceOrderMechanic {
            $locked = ServiceOrder::query()
                ->whereKey($serviceOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAssignment = $locked->mechanicAssignments()
                ->whereKey($assignment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAssignment->unassigned_at !== null) {
                throw ValidationException::withMessages([
                    'mechanic_id' => 'Mekanik sudah dilepas dari work order ini.',
                ]);
            }

            if ($locked->status->isLocked()) {
                throw ValidationException::withMessages([
                    'mechanic_id' => 'Penugasan tidak dapat diubah setelah work order selesai.',
                ]);
            }

            $lockedAssignment->update(['unassigned_at' => now()]);

            return $lockedAssignment;
        });
    }
}
