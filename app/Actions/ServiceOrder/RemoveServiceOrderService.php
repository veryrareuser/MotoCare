<?php

namespace App\Actions\ServiceOrder;

use App\Actions\Billing\SyncInvoiceItems;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RemoveServiceOrderService
{
    public function __construct(
        private SyncInvoiceItems $syncInvoiceItems,
    ) {}

    /**
     * Remove a service line from an open work order.
     *
     * @throws ValidationException
     */
    public function handle(ServiceOrder $serviceOrder, ServiceOrderService $line): void
    {
        DB::transaction(function () use ($serviceOrder, $line): void {
            $locked = ServiceOrder::query()
                ->whereKey($serviceOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status->isLocked()) {
                throw ValidationException::withMessages([
                    'service' => 'Layanan tidak dapat dihapus setelah work order selesai.',
                ]);
            }

            $invoice = $locked->invoice()->first();

            if ($invoice instanceof Invoice && (float) $invoice->paid_amount > 0) {
                throw ValidationException::withMessages([
                    'service' => 'Layanan tidak dapat dihapus karena invoice sudah memiliki pembayaran.',
                ]);
            }

            $locked->services()
                ->whereKey($line->getKey())
                ->lockForUpdate()
                ->firstOrFail()
                ->delete();

            $this->syncInvoice($locked);
        });
    }

    private function syncInvoice(ServiceOrder $serviceOrder): void
    {
        $invoice = $serviceOrder->invoice()->first();

        if ($invoice instanceof Invoice) {
            $this->syncInvoiceItems->handle($invoice, $serviceOrder);
        }
    }
}
