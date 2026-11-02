<?php

namespace App\Http\Controllers\Staff;

use App\Actions\Billing\RecordInvoicePayment;
use App\Actions\Billing\SetInvoiceDeposit;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoicePaymentRequest;
use App\Http\Requests\UpdateInvoiceDepositRequest;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Display the invoices created on the selected date.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Invoice::class);

        $date = $this->resolveDateFilter($request->query('date'));
        $status = InvoiceStatus::tryFrom($request->string('status')->toString());
        $search = $request->string('search')->trim()->toString();

        $baseQuery = Invoice::query()->forDate($date);

        $invoices = (clone $baseQuery)
            ->with(['serviceOrder.customer', 'serviceOrder.motorcycle'])
            ->when($status !== null, fn (Builder $query) => $query->where('payment_status', $status->value))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('invoice_number', 'like', '%'.$search.'%')
                        ->orWhereHas('serviceOrder', function (Builder $query) use ($search): void {
                            $query->where('work_order_number', 'like', '%'.$search.'%')
                                ->orWhereHas('customer', fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'))
                                ->orWhereHas('motorcycle', fn (Builder $query) => $query->where('license_plate', 'like', '%'.$search.'%'));
                        });
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $counts = (clone $baseQuery)
            ->selectRaw('payment_status, COUNT(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        return view('staff.invoices.index', [
            'invoices' => $invoices,
            'date' => $date,
            'status' => $status,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    /**
     * Show an invoice with its items and payments.
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

        return view('staff.invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show the printable receipt for an invoice.
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
            'backUrl' => route('staff.invoices.show', $invoice),
            'backLabel' => 'Kembali ke invoice',
        ]);
    }

    /**
     * Enable or disable the optional deposit.
     */
    public function updateDeposit(
        UpdateInvoiceDepositRequest $request,
        Invoice $invoice,
        SetInvoiceDeposit $setInvoiceDeposit,
    ): RedirectResponse {
        $setInvoiceDeposit->handle(
            $invoice,
            $request->boolean('deposit_required'),
            $request->filled('deposit_percentage') ? (float) $request->input('deposit_percentage') : null,
        );

        return redirect()
            ->route('staff.invoices.show', $invoice)
            ->with('success', 'Pengaturan deposit diperbarui.');
    }

    /**
     * Record a manual payment against the invoice.
     */
    public function storePayment(
        StoreInvoicePaymentRequest $request,
        Invoice $invoice,
        RecordInvoicePayment $recordInvoicePayment,
    ): RedirectResponse {
        $payment = $recordInvoicePayment->handle(
            $invoice,
            (float) $request->input('amount'),
            PaymentMethod::from($request->string('payment_method')->toString()),
            $request->user(),
            $request->string('notes')->trim()->toString() ?: null,
        );

        return redirect()
            ->route('staff.invoices.show', $invoice)
            ->with('success', 'Pembayaran '.$payment->payment_reference.' berhasil dicatat.');
    }
}
