<?php

namespace App\Models;

use Database\Factories\SparepartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'sku',
    'name',
    'description',
    'unit',
    'unit_price',
    'stock_quantity',
    'minimum_stock',
    'is_active',
])]
class Sparepart extends Model
{
    /** @use HasFactory<SparepartFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'stock_quantity' => 'decimal:2',
            'minimum_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<MotorcycleCompatibility, $this>
     */
    public function compatibilities(): HasMany
    {
        return $this->hasMany(MotorcycleCompatibility::class);
    }

    /**
     * @return HasMany<InventoryMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * @return HasMany<ServiceOrderPart, $this>
     */
    public function serviceOrderParts(): HasMany
    {
        return $this->hasMany(ServiceOrderPart::class);
    }

    /**
     * Scope the query to spareparts that are still sold.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope the query to spareparts at or below their minimum stock.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
    }

    public function isLowStock(): bool
    {
        return (float) $this->stock_quantity <= (float) $this->minimum_stock;
    }

    public function formattedPrice(): string
    {
        return 'Rp '.number_format((float) $this->unit_price, 0, ',', '.');
    }

    public function formattedStock(): string
    {
        return number_format((float) $this->stock_quantity, 0, ',', '.').' '.$this->unit;
    }
}
