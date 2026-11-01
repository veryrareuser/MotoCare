<?php

namespace App\Models;

use App\Enums\InventoryMovementType;
use Database\Factories\InventoryMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sparepart_id',
    'movement_type',
    'quantity',
    'quantity_before',
    'quantity_after',
    'reference_type',
    'reference_id',
    'performed_by',
    'notes',
])]
class InventoryMovement extends Model
{
    /** @use HasFactory<InventoryMovementFactory> */
    use HasFactory;

    /**
     * The model only records when a movement happened.
     */
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'movement_type' => InventoryMovementType::class,
            'quantity' => 'decimal:2',
            'quantity_before' => 'decimal:2',
            'quantity_after' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Sparepart, $this>
     */
    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Whether the movement increased the stock balance.
     */
    public function isIncrease(): bool
    {
        return (float) $this->quantity_after > (float) $this->quantity_before;
    }

    public function formattedDelta(): string
    {
        $delta = (float) $this->quantity_after - (float) $this->quantity_before;
        $sign = $delta >= 0 ? '+' : '−';

        return $sign.number_format(abs($delta), 0, ',', '.');
    }
}
