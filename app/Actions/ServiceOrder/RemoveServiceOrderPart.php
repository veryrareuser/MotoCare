<?php

namespace App\Actions\ServiceOrder;

use App\Actions\Billing\SyncInvoiceItems;
use App\Actions\Inventory\RecordStockMovement;
use App\Enums\InventoryMovementType;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderPart;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RemoveServiceOrderPart
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
        private SyncInvoiceItems $syncInvoiceItems,
    ) {}

    /**
     * Remove a sparepart line and return its quantity to stock.
     *
     * The original stock-out movement is never edited. A compensating
     * adjustment movement is posted so the ledger keeps the full history.
     *
     * @throws ValidationException
     */
    public function handle(ServiceOrder $serviceOrder, ServiceOrderPart $line, User $actor): void
    {
        DB::transaction(function () use ($serviceOrder, $line, $actor): void {
            $locked = ServiceOrder::query()
                ->whereKey($serviceOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status->isLocked()) {
                throw ValidationException::withMessages([
                    'part' => 'Sparepart tidak dapat dihapus setelah work order selesai.',
                ]);
            }

            $invoice = $locked->invoice()->first();

            if ($invoice instanceof Invoice && (float) $invoice->paid_amount > 0) {
                throw ValidationException::withMessages([
                    'part' => 'Sparepart tidak dapat dihapus karena invoice sudah memiliki pembayaran.',
                ]);
            }

            $lockedLine = $locked->parts()
                ->whereKey($line->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedLine->inventory_movement_id !== null) {
                $sparepart = Sparepart::withTrashed()
                    ->whereKey($lockedLine->sparepart_id)
                    ->firstOrFail();

                $this->recordStockMovement->handle(
                    $sparepart,
                    InventoryMovementType::Adjustment,
                    (float) $lockedLine->quantity,
                    $actor,
                    'Pembatalan pemakaian pada work order '.$locked->work_order_number.'.',
                    true,
                    'service_order_part_removal',
                    $lockedLine->getKey(),
                    'part',
                );
            }

            $lockedLine->delete();

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
