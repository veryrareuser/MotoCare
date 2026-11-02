<?php

namespace App\Actions\ServiceOrder;

use App\Enums\MechanicAssignmentRole;
use App\Enums\UserRole;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMechanic;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignServiceOrderMechanic
{
    /**
     * Assign a mechanic to a work order.
     *
     * The work order row is locked so two simultaneous assignments cannot
     * both pass the duplicate guard. Reassignment after an unassignment is
     * allowed; the previous row keeps its unassigned timestamp as history.
     *
     * @throws ValidationException
     */
    public function handle(
        ServiceOrder $serviceOrder,
        User $mechanic,
        ?MechanicAssignmentRole $role = null,
    ): ServiceOrderMechanic {
        return DB::transaction(function () use ($serviceOrder, $mechanic, $role): ServiceOrderMechanic {
            $locked = ServiceOrder::query()
                ->whereKey($serviceOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status->isLocked()) {
                throw ValidationException::withMessages([
                    'mechanic_id' => 'Mekanik tidak dapat ditugaskan pada work order yang sudah selesai.',
                ]);
            }

            if ($mechanic->role !== UserRole::Mechanic || ! $mechanic->is_active) {
                throw ValidationException::withMessages([
                    'mechanic_id' => 'Pilih mekanik yang aktif.',
                ]);
            }

            $alreadyAssigned = $locked->mechanicAssignments()
                ->where('mechanic_id', $mechanic->getKey())
                ->whereNull('unassigned_at')
                ->exists();

            if ($alreadyAssigned) {
                throw ValidationException::withMessages([
                    'mechanic_id' => 'Mekanik ini sudah ditugaskan pada work order.',
                ]);
            }

            return $locked->mechanicAssignments()->create([
                'mechanic_id' => $mechanic->getKey(),
                'assignment_role' => $role,
                'assigned_at' => now(),
            ]);
        });
    }
}
