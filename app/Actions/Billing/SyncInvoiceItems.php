<?php

namespace App\Actions\Billing;

use App\Enums\InvoiceItemType;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SyncInvoiceItems
{
    /**
     * Rebuild a draft invoice from the work order's current price snapshots.
     *
     * Finalized invoices are immutable: once the work order is completed the
     * invoice is finalized and this action becomes a no-op.
     *
     * @throws ValidationException
     */
    public function handle(Invoice $invoice, ServiceOrder $serviceOrder): Invoice
    {
        return DB::transaction(function () use ($invoice, $serviceOrder): Invoice {
            $locked = Invoice::query()
                ->whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isDraft()) {
                return $locked->load('items');
            }

            $locked->items()->delete();

            $items = [];
            $subtotal = 0.0;

            foreach ($serviceOrder->services()->get() as $service) {
                $lineTotal = $this->lineTotal($service->quantity, $service->unit_price_snapshot);
                $subtotal += $lineTotal;

                $items[] = [
                    'item_type' => InvoiceItemType::Service,
                    'description' => $service->description,
                    'quantity' => $service->quantity,
                    'unit_price' => $service->unit_price_snapshot,
                    'line_total' => $lineTotal,
                    'service_type_id' => $service->service_type_id,
                    'sparepart_id' => null,
                ];
            }

            foreach ($serviceOrder->parts()->with('sparepart')->get() as $part) {
                $lineTotal = $this->lineTotal($part->quantity, $part->unit_price_snapshot);
                $subtotal += $lineTotal;

                $items[] = [
                    'item_type' => InvoiceItemType::Sparepart,
                    'description' => $part->sparepart->name,
                    'quantity' => $part->quantity,
                    'unit_price' => $part->unit_price_snapshot,
                    'line_total' => $lineTotal,
                    'service_type_id' => null,
                    'sparepart_id' => $part->sparepart_id,
                ];
            }

            $subtotal = round($subtotal, 2);
            $total = round($subtotal - (float) $locked->discount_amount + (float) $locked->tax_amount, 2);
            $paid = round((float) $locked->paid_amount, 2);

            if ($total < $paid) {
                throw ValidationException::withMessages([
                    'invoice' => 'Total invoice tidak boleh lebih kecil dari jumlah yang sudah dibayar.',
                ]);
            }

            $locked->items()->createMany($items);

            $depositAmount = $locked->deposit_required && $locked->deposit_percentage !== null
                ? round($total * (float) $locked->deposit_percentage / 100, 2)
                : null;

            $locked->update([
                'subtotal' => $subtotal,
                'total_amount' => $total,
                'remaining_amount' => round($total - $paid, 2),
                'payment_status' => InvoiceStatus::forAmounts($paid, $total),
                'deposit_amount' => $depositAmount,
            ]);

            return $locked->load('items');
        });
    }

    private function lineTotal(float|string $quantity, float|string $unitPrice): float
    {
        return round((float) $quantity * (float) $unitPrice, 2);
    }
}
