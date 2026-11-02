<?php

namespace App\Actions\Billing;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;

class CreateInvoiceForServiceOrder
{
    public function __construct(
        private NextInvoiceNumber $nextInvoiceNumber,
        private SyncInvoiceItems $syncInvoiceItems,
    ) {}

    /**
     * Create the draft invoice for a work order, or return the existing one.
     *
     * The service order is locked so concurrent check-ins cannot create two
     * invoices. Invoice items are copied from the work order's price
     * snapshots, never from the current catalog.
     */
    public function handle(ServiceOrder $serviceOrder): Invoice
    {
        return DB::transaction(function () use ($serviceOrder): Invoice {
            $locked = ServiceOrder::query()
                ->whereKey($serviceOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $invoice = Invoice::query()
                ->where('service_order_id', $locked->getKey())
                ->lockForUpdate()
                ->first();

            if ($invoice instanceof Invoice) {
                return $this->syncInvoiceItems->handle($invoice, $locked);
            }

            $number = $this->nextInvoiceNumber->handle();

            $invoice = Invoice::query()->create([
                'invoice_number' => $number['number'],
                'service_order_id' => $locked->getKey(),
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'deposit_required' => false,
                'deposit_percentage' => null,
                'deposit_amount' => null,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'payment_status' => InvoiceStatus::Unpaid,
                'issued_at' => null,
                'finalized_at' => null,
            ]);

            return $this->syncInvoiceItems->handle($invoice, $locked);
        });
    }
}
