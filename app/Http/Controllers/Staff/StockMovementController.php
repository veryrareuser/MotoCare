<?php

namespace App\Http\Controllers\Staff;

use App\Actions\Inventory\RecordStockMovement;
use App\Enums\InventoryMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StockMovementRequest;
use App\Models\Sparepart;
use Illuminate\Http\RedirectResponse;

class StockMovementController extends Controller
{
    /**
     * Post a manual stock movement for the sparepart.
     */
    public function store(
        StockMovementRequest $request,
        Sparepart $sparepart,
        RecordStockMovement $recordStockMovement,
    ): RedirectResponse {
        $type = InventoryMovementType::from($request->string('movement_type')->toString());
        $increases = $request->string('direction')->toString() !== 'decrease';

        $movement = $recordStockMovement->handle(
            $sparepart,
            $type,
            (float) $request->float('quantity'),
            $request->user(),
            $request->string('notes')->trim()->toString() ?: null,
            $increases,
        );

        return redirect()
            ->route('staff.spareparts.show', $sparepart)
            ->with('success', 'Stok diperbarui menjadi '.$movement->quantity_after.' '.$sparepart->unit.'.');
    }
}
