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

class AddServiceOrderPart
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
        private SyncInvoiceItems $syncInvoiceItems,
    ) {}

    /**
     * Add a sparepart line and deduct its stock in the same transaction.
     *
     * The part line is created first so the inventory movement can reference
     * it. If stock is insufficient the movement action throws and the whole
     * transaction rolls back, leaving no orphan line.
     *
     * @throws ValidationException
     */
    public function handle(
        ServiceOrder $serviceOrder,
        Sparepart $sparepart,
        float $quantity,
        User $actor,
    ): ServiceOrderPart {
        return DB::transaction(function () use ($serviceOrder, $sparepart, $quantity, $actor): ServiceOrderPart {
            $locked = ServiceOrder::query()
                ->whereKey($serviceOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status->isLocked()) {
                throw ValidationException::withMessages([
                    'sparepart_id' => 'Sparepart tidak dapat ditambahkan setelah work order selesai.',
                ]);
            }

            if ($sparepart->trashed() || ! $sparepart->is_active) {
                throw ValidationException::withMessages([
                    'sparepart_id' => 'Sparepart yang dipilih sudah tidak tersedia.',
                ]);
            }

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'part_quantity' => 'Jumlah sparepart harus lebih dari nol.',
                ]);
            }

            $line = $locked->parts()->create([
                'sparepart_id' => $sparepart->getKey(),
                'quantity' => $quantity,
                'unit_price_snapshot' => $sparepart->unit_price,
            ]);

            $movement = $this->recordStockMovement->handle(
                $sparepart,
                InventoryMovementType::Out,
                $quantity,
                $actor,
                'Pemakaian pada work order '.$locked->work_order_number.'.',
                true,
                'service_order_part',
                $line->getKey(),
                'part_quantity',
            );

            $line->update(['inventory_movement_id' => $movement->getKey()]);

            $this->syncInvoice($locked);

            return $line->load('sparepart');
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
