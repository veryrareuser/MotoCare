<?php

namespace App\Models;

use Database\Factories\ServiceOrderServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_order_id',
    'service_type_id',
    'description',
    'quantity',
    'unit_price_snapshot',
    'duration_snapshot_minutes',
])]
class ServiceOrderService extends Model
{
    /** @use HasFactory<ServiceOrderServiceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price_snapshot' => 'decimal:2',
            'duration_snapshot_minutes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ServiceOrder, $this>
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * @return BelongsTo<ServiceType, $this>
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class)->withTrashed();
    }

    public function lineTotal(): float
    {
        return (float) $this->quantity * (float) $this->unit_price_snapshot;
    }

    public function formattedPrice(): string
    {
        return 'Rp '.number_format((float) $this->unit_price_snapshot, 0, ',', '.');
    }

    public function formattedLineTotal(): string
    {
        return 'Rp '.number_format($this->lineTotal(), 0, ',', '.');
    }

    public function formattedQuantity(): string
    {
        return rtrim(rtrim(number_format((float) $this->quantity, 2, ',', '.'), '0'), ',');
    }

    public function formattedDuration(): string
    {
        return $this->duration_snapshot_minutes === null
            ? 'Tanpa estimasi'
            : $this->duration_snapshot_minutes.' menit';
    }
}
