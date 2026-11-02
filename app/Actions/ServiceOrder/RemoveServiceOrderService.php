<?php

namespace App\Actions\ServiceOrder;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RemoveServiceOrderService
{
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

            $locked->services()
                ->whereKey($line->getKey())
                ->lockForUpdate()
                ->firstOrFail()
                ->delete();
        });
    }
}
