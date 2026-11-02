<?php

namespace App\Http\Controllers\Staff;

use App\Actions\ServiceOrder\AssignServiceOrderMechanic;
use App\Actions\ServiceOrder\UnassignServiceOrderMechanic;
use App\Enums\MechanicAssignmentRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignServiceOrderMechanicRequest;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMechanic;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ServiceOrderMechanicController extends Controller
{
    /**
     * Assign a mechanic to the work order.
     */
    public function store(
        AssignServiceOrderMechanicRequest $request,
        ServiceOrder $serviceOrder,
        AssignServiceOrderMechanic $assignServiceOrderMechanic,
    ): RedirectResponse {
        $mechanic = User::query()->findOrFail($request->integer('mechanic_id'));

        $role = $request->filled('assignment_role')
            ? MechanicAssignmentRole::from($request->string('assignment_role')->toString())
            : null;

        $assignServiceOrderMechanic->handle($serviceOrder, $mechanic, $role);

        return back()->with('success', $mechanic->name.' ditugaskan ke work order ini.');
    }

    /**
     * Release a mechanic from the work order.
     */
    public function destroy(
        ServiceOrder $serviceOrder,
        ServiceOrderMechanic $assignment,
        UnassignServiceOrderMechanic $unassignServiceOrderMechanic,
    ): RedirectResponse {
        Gate::authorize('assignMechanic', $serviceOrder);

        abort_unless($assignment->service_order_id === $serviceOrder->getKey(), 404);

        $unassignServiceOrderMechanic->handle($serviceOrder, $assignment);

        return back()->with('success', 'Mekanik dilepas dari work order ini.');
    }
}
