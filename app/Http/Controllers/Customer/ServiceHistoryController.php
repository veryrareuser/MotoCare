<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ServiceOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceHistoryController extends Controller
{
    /**
     * Display work orders that are still in the workshop.
     */
    public function active(Request $request): View
    {
        $serviceOrders = ServiceOrder::query()
            ->where('customer_id', $request->user()->getKey())
            ->active()
            ->with(['motorcycle', 'services.serviceType', 'parts.sparepart', 'activeMechanicAssignments.mechanic', 'invoice'])
            ->orderByDesc('checked_in_at')
            ->orderByDesc('id')
            ->get();

        return view('customer.service-orders.active', [
            'serviceOrders' => $serviceOrders,
        ]);
    }

    /**
     * Display completed work orders.
     */
    public function index(Request $request): View
    {
        $serviceOrders = ServiceOrder::query()
            ->where('customer_id', $request->user()->getKey())
            ->whereIn('status', [ServiceOrderStatus::Completed, ServiceOrderStatus::PickedUp])
            ->with(['motorcycle', 'services.serviceType', 'parts.sparepart', 'invoice'])
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();

        return view('customer.service-orders.index', [
            'serviceOrders' => $serviceOrders,
        ]);
    }

    /**
     * Show one of the customer's work orders with its snapshots and history.
     */
    public function show(ServiceOrder $serviceOrder): View
    {
        Gate::authorize('viewHistory', $serviceOrder);

        $serviceOrder->load([
            'motorcycle',
            'services.serviceType',
            'parts.sparepart',
            'activeMechanicAssignments.mechanic',
            'statusLogs.changedBy',
            'invoice',
        ]);

        return view('customer.service-orders.show', [
            'serviceOrder' => $serviceOrder,
        ]);
    }
}
