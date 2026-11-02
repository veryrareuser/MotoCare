<?php

namespace App\Models;

use Database\Factories\MotorcycleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'brand', 'model', 'year', 'license_plate', 'odometer_current', 'notes'])]
class Motorcycle extends Model
{
    /** @use HasFactory<MotorcycleFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The maximum number of active motorcycles a customer may register.
     */
    public const MAX_PER_CUSTOMER = 5;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'odometer_current' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function displayName(): string
    {
        return trim($this->brand.' '.$this->model);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * @return HasMany<ServiceOrder, $this>
     */
    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
