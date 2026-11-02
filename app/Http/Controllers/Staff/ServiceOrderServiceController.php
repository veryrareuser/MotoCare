<?php

namespace App\Http\Controllers\Staff;

use App\Actions\ServiceOrder\AddServiceOrderService;
use App\Actions\ServiceOrder\RemoveServiceOrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceOrderServiceRequest;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderService;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ServiceOrderServiceController extends Controller
{
    /**
     * Record a service performed on the work order.
     */
    public function store(
        StoreServiceOrderServiceRequest $request,
        ServiceOrder $serviceOrder,
        AddServiceOrderService $addServiceOrderService,
    ): RedirectResponse {
        $serviceType = ServiceType::query()->findOrFail($request->integer('service_type_id'));

        $addServiceOrderService->handle(
            $serviceOrder,
            $serviceType,
            $request->float('quantity'),
        );

        return back()->with('success', 'Layanan ditambahkan ke work order.');
    }

    /**
     * Remove a service line from the work order.
     */
    public function destroy(
        ServiceOrder $serviceOrder,
        ServiceOrderService $line,
        RemoveServiceOrderService $removeServiceOrderService,
    ): RedirectResponse {
        Gate::authorize('recordService', $serviceOrder);

        abort_unless($line->service_order_id === $serviceOrder->getKey(), 404);

        $removeServiceOrderService->handle($serviceOrder, $line);

        return back()->with('success', 'Layanan dihapus dari work order.');
    }
}
