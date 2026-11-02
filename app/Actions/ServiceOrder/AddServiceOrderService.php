<?php

namespace App\Actions\ServiceOrder;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderService;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddServiceOrderService
{
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

            return $locked->services()->create([
                'service_type_id' => $serviceType->getKey(),
                'description' => $serviceType->name,
                'quantity' => $quantity,
                'unit_price_snapshot' => $serviceType->base_price,
                'duration_snapshot_minutes' => $serviceType->estimated_duration_minutes,
            ]);
        });
    }
}
