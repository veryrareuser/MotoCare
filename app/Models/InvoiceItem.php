<?php

namespace App\Models;

use App\Enums\InvoiceItemType;
use Database\Factories\InvoiceItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'invoice_id',
    'item_type',
    'description',
    'quantity',
    'unit_price',
    'line_total',
    'service_type_id',
    'sparepart_id',
])]
class InvoiceItem extends Model
{
    /** @use HasFactory<InvoiceItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'item_type' => InvoiceItemType::class,
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<ServiceType, $this>
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class)->withTrashed();
    }

    /**
     * @return BelongsTo<Sparepart, $this>
     */
    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class)->withTrashed();
    }

    public function typeLabel(): string
    {
        return $this->item_type->label();
    }

    public function formattedQuantity(): string
    {
        return rtrim(rtrim(number_format((float) $this->quantity, 2, ',', '.'), '0'), ',');
    }

    public function formattedUnitPrice(): string
    {
        return 'Rp '.number_format((float) $this->unit_price, 0, ',', '.');
    }

    public function formattedLineTotal(): string
    {
        return 'Rp '.number_format((float) $this->line_total, 0, ',', '.');
    }
}
