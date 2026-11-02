<?php

namespace App\Actions\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordStockMovement
{
    /**
     * Post a stock movement and keep the balance and ledger in sync.
     *
     * The sparepart row is locked so two concurrent movements cannot both read
     * the same balance and overwrite each other.
     *
     * @throws ValidationException
     */
    public function handle(
        Sparepart $sparepart,
        InventoryMovementType $type,
        float $quantity,
        User $performedBy,
        ?string $notes = null,
        bool $increases = true,
        ?string $referenceType = null,
        ?int $referenceId = null,
        string $errorKey = 'quantity',
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                $errorKey => 'Jumlah harus lebih dari nol.',
            ]);
        }

        return DB::transaction(function () use ($sparepart, $type, $quantity, $performedBy, $notes, $increases, $referenceType, $referenceId, $errorKey): InventoryMovement {
            $locked = Sparepart::query()
                ->whereKey($sparepart->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $before = (float) $locked->stock_quantity;

            $delta = match ($type) {
                InventoryMovementType::In => $quantity,
                InventoryMovementType::Out => -$quantity,
                InventoryMovementType::Adjustment => $increases ? $quantity : -$quantity,
            };

            $after = $before + $delta;

            if ($after < 0) {
                throw ValidationException::withMessages([
                    $errorKey => 'Stok tidak mencukupi. Sisa stok saat ini '.$this->formatQuantity($before).'.',
                ]);
            }

            $locked->update(['stock_quantity' => $after]);

            $movement = $locked->movements()->create([
                'movement_type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'performed_by' => $performedBy->getKey(),
                'notes' => $notes,
            ]);

            $sparepart->setAttribute('stock_quantity', $locked->stock_quantity);

            return $movement;
        });
    }

    private function formatQuantity(float $quantity): string
    {
        return number_format($quantity, 0, ',', '.');
    }
}
