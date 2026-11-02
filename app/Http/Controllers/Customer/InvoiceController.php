<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Display the customer's invoices.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->forCustomer($request->user())
            ->with(['serviceOrder.customer', 'serviceOrder.motorcycle'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('customer.invoices.index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show one of the customer's invoices.
     */
    public function show(Invoice $invoice): View
    {
        Gate::authorize('view', $invoice);

        $invoice->load([
            'serviceOrder.customer',
            'serviceOrder.motorcycle',
            'items.serviceType',
            'items.sparepart',
            'payments.receivedBy',
        ]);

        return view('customer.invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show the printable receipt for one of the customer's invoices.
     */
    public function receipt(Invoice $invoice): View
    {
        Gate::authorize('view', $invoice);

        $invoice->load([
            'serviceOrder.customer',
            'serviceOrder.motorcycle',
            'items',
            'payments.receivedBy',
        ]);

        return view('invoices.receipt', [
            'invoice' => $invoice,
            'backUrl' => route('invoices.show', $invoice),
            'backLabel' => 'Kembali ke invoice',
        ]);
    }
}
