<?php

namespace App\Http\Controllers\Staff;

use App\Actions\ServiceOrder\CreateWalkInServiceOrder;
use App\Actions\ServiceOrder\TransitionServiceOrderStatus;
use App\Enums\ServiceOrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWalkInServiceOrderRequest;
use App\Http\Requests\UpdateServiceOrderStatusRequest;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use App\Models\ServiceType;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceOrderController extends Controller
{
    /**
     * Display the work orders checked in on the selected date.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ServiceOrder::class);

        $date = $this->resolveDateFilter($request->query('date'));
        $status = ServiceOrderStatus::tryFrom($request->string('status')->toString());
        $search = $request->string('search')->trim()->toString();

        $baseQuery = ServiceOrder::query()->forDate($date);

        $serviceOrders = (clone $baseQuery)
            ->with([
                'booking',
                'customer',
                'motorcycle',
                'activeMechanicAssignments.mechanic',
            ])
            ->withCount(['services', 'parts'])
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('work_order_number', 'like', '%'.$search.'%')
                        ->orWhereHas('booking', fn (Builder $query) => $query->where('queue_number', 'like', '%'.$search.'%'))
                        ->orWhereHas('customer', fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('motorcycle', fn (Builder $query) => $query->where('license_plate', 'like', '%'.$search.'%'));
                });
            })
            ->orderByDesc('checked_in_at')
            ->orderByDesc('id')
            ->get();

        $counts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('staff.service-orders.index', [
            'serviceOrders' => $serviceOrders,
            'date' => $date,
            'status' => $status,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    /**
     * Show the walk-in intake form.
     */
    public function create(): View
    {
        Gate::authorize('create', ServiceOrder::class);

        $customers = User::query()
            ->where('role', UserRole::Customer)
            ->where('is_active', true)
            ->with(['motorcycles' => fn ($query) => $query->orderBy('brand')->orderBy('model')])
            ->orderBy('name')
            ->get()
            ->filter(fn (User $customer): bool => $customer->motorcycles->isNotEmpty())
            ->values();

        $serviceTypes = ServiceType::query()
            ->active()
            ->orderBy('name')
            ->get();

        return view('staff.service-orders.create', [
            'customers' => $customers,
            'serviceTypes' => $serviceTypes,
        ]);
    }

    /**
     * Create a work order for a walk-in customer.
     */
    public function store(
        StoreWalkInServiceOrderRequest $request,
        CreateWalkInServiceOrder $createWalkInServiceOrder,
    ): RedirectResponse {
        $motorcycle = Motorcycle::query()->findOrFail($request->integer('motorcycle_id'));

        $serviceOrder = $createWalkInServiceOrder->handle(
            $request->user(),
            $motorcycle,
            $request->array('service_type_ids'),
            $request->integer('odometer_in'),
            $request->string('notes')->trim()->toString() ?: null,
        );

        return redirect()
            ->route('staff.service-orders.show', $serviceOrder)
            ->with('success', 'Work order '.$serviceOrder->work_order_number.' berhasil dibuat.');
    }

    /**
     * Show a work order with its mechanics, services, parts, and history.
     */
    public function show(ServiceOrder $serviceOrder): View
    {
        Gate::authorize('view', $serviceOrder);

        $serviceOrder->load([
            'booking',
            'customer',
            'motorcycle',
            'mechanicAssignments.mechanic',
            'activeMechanicAssignments.mechanic',
            'services.serviceType',
            'parts.sparepart',
            'statusLogs.changedBy',
            'invoice',
        ]);

        $assignedMechanicIds = $serviceOrder->activeMechanicAssignments
            ->pluck('mechanic_id')
            ->all();

        $availableMechanics = User::query()
            ->where('role', UserRole::Mechanic)
            ->where('is_active', true)
            ->whereNotIn('id', $assignedMechanicIds)
            ->orderBy('name')
            ->get();

        $serviceTypes = ServiceType::query()
            ->active()
            ->orderBy('name')
            ->get();

        $spareparts = Sparepart::query()
            ->active()
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        return view('staff.service-orders.show', [
            'serviceOrder' => $serviceOrder,
            'availableMechanics' => $availableMechanics,
            'serviceTypes' => $serviceTypes,
            'spareparts' => $spareparts,
            'allowedStatuses' => $serviceOrder->status->allowedNextStatuses(),
        ]);
    }

    /**
     * Move the work order to its next status.
     */
    public function updateStatus(
        UpdateServiceOrderStatusRequest $request,
        ServiceOrder $serviceOrder,
        TransitionServiceOrderStatus $transitionServiceOrderStatus,
    ): RedirectResponse {
        $status = ServiceOrderStatus::from($request->string('status')->toString());
        $odometerOut = $request->filled('odometer_out')
            ? $request->integer('odometer_out')
            : null;

        $transitionServiceOrderStatus->handle(
            $serviceOrder,
            $status,
            $request->user(),
            $request->string('notes')->trim()->toString() ?: null,
            $odometerOut,
        );

        return redirect()
            ->route('staff.service-orders.show', $serviceOrder)
            ->with('success', 'Status work order diperbarui menjadi '.$status->label().'.');
    }
}
