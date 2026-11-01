<?php

namespace App\Models;

use Database\Factories\ServiceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description', 'base_price', 'estimated_duration_minutes', 'is_active'])]
class ServiceType extends Model
{
    /** @use HasFactory<ServiceTypeFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'estimated_duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope the query to services that can be booked.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return HasMany<BookingService, $this>
     */
    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    public function formattedPrice(): string
    {
        return 'Rp '.number_format((float) $this->base_price, 0, ',', '.');
    }

    public function formattedDuration(): string
    {
        if ($this->estimated_duration_minutes === null) {
            return 'Tanpa estimasi';
        }

        return $this->estimated_duration_minutes.' menit';
    }
}
