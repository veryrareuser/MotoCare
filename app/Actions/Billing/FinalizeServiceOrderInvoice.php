<?php

namespace App\Actions\Billing;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinalizeServiceOrderInvoice
{
    public function __construct(
        private CreateInvoiceForServiceOrder $createInvoice,
        private SyncInvoiceItems $syncInvoiceItems,
    ) {}

    /**
     * Finalize the draft invoice when its work order is completed.
     *
     * The invoice items are rebuilt from the actual work order lines one last
     * time, then the document is locked against future changes.
     *
     * @throws ValidationException
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

            if (! $invoice instanceof Invoice) {
                $created = $this->createInvoice->handle($locked);

                $invoice = Invoice::query()
                    ->whereKey($created->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $invoice = $this->syncInvoiceItems->handle($invoice, $locked);

            if (! $invoice->isDraft()) {
                return $invoice;
            }

            $paid = round((float) $invoice->paid_amount, 2);
            $total = round((float) $invoice->total_amount, 2);

            if ($paid > $total) {
                throw ValidationException::withMessages([
                    'invoice' => 'Total invoice tidak boleh lebih kecil dari jumlah yang sudah dibayar.',
                ]);
            }

            $invoice->update([
                'issued_at' => $locked->completed_at ?? now(),
                'finalized_at' => now(),
                'remaining_amount' => round($total - $paid, 2),
                'payment_status' => InvoiceStatus::forAmounts($paid, $total),
            ]);

            return $invoice->load(['items', 'payments']);
        });
    }
}
