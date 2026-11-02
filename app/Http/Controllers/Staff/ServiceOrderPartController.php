<?php

namespace App\Http\Controllers\Staff;

use App\Actions\ServiceOrder\AddServiceOrderPart;
use App\Actions\ServiceOrder\RemoveServiceOrderPart;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceOrderPartRequest;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderPart;
use App\Models\Sparepart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceOrderPartController extends Controller
{
    /**
     * Record a sparepart consumed by the work order.
     */
    public function store(
        StoreServiceOrderPartRequest $request,
        ServiceOrder $serviceOrder,
        AddServiceOrderPart $addServiceOrderPart,
    ): RedirectResponse {
        $sparepart = Sparepart::query()->findOrFail($request->integer('sparepart_id'));

        $addServiceOrderPart->handle(
            $serviceOrder,
            $sparepart,
            $request->float('part_quantity'),
            $request->user(),
        );

        return back()->with('success', 'Sparepart ditambahkan dan stok dipotong.');
    }

    /**
     * Remove a sparepart line and return its quantity to stock.
     */
    public function destroy(
        Request $request,
        ServiceOrder $serviceOrder,
        ServiceOrderPart $line,
        RemoveServiceOrderPart $removeServiceOrderPart,
    ): RedirectResponse {
        Gate::authorize('recordPart', $serviceOrder);

        abort_unless($line->service_order_id === $serviceOrder->getKey(), 404);

        $removeServiceOrderPart->handle($serviceOrder, $line, $request->user());

        return back()->with('success', 'Sparepart dihapus dan stok dikembalikan.');
    }
}
