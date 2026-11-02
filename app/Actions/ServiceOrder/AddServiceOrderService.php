<?php

namespace App\Actions\ServiceOrder;

use App\Actions\Billing\SyncInvoiceItems;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderService;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddServiceOrderService
{
    public function __construct(
        private SyncInvoiceItems $syncInvoiceItems,
    ) {}

    /**
     * Add a service line with a snapshot of the current catalog values.
     *
     * @throws ValidationException
     */
    public function handle(
        ServiceOrder $serviceOrder,
        ServiceType $serviceType,
        float $quantity,
    ): ServiceOrderService {
        return DB::transaction(function () use ($serviceOrder, $serviceType, $quantity): ServiceOrderService {
            $locked = ServiceOrder::query()
                ->whereKey($serviceOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status->isLocked()) {
                throw ValidationException::withMessages([
                    'service_type_id' => 'Layanan tidak dapat ditambahkan setelah work order selesai.',
                ]);
            }

            if ($serviceType->trashed() || ! $serviceType->is_active) {
                throw ValidationException::withMessages([
                    'service_type_id' => 'Layanan yang dipilih sudah tidak tersedia.',
                ]);
            }

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah layanan harus lebih dari nol.',
                ]);
            }

            $line = $locked->services()->create([
                'service_type_id' => $serviceType->getKey(),
                'description' => $serviceType->name,
                'quantity' => $quantity,
                'unit_price_snapshot' => $serviceType->base_price,
                'duration_snapshot_minutes' => $serviceType->estimated_duration_minutes,
            ]);

            $this->syncInvoice($locked);

            return $line;
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
