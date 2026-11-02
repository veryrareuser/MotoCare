<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Carbon\CarbonImmutable;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'booking_number',
    'user_id',
    'motorcycle_id',
    'booking_slot_id',
    'scheduled_at',
    'queue_date',
    'queue_number',
    'status',
    'customer_note',
    'confirmed_at',
    'checked_in_at',
    'cancelled_at',
    'cancellation_reason',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'queue_date' => 'date',
            'status' => BookingStatus::class,
            'confirmed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Motorcycle, $this>
     */
    public function motorcycle(): BelongsTo
    {
        return $this->belongsTo(Motorcycle::class);
    }

    /**
     * @return BelongsTo<BookingSlot, $this>
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(BookingSlot::class, 'booking_slot_id');
    }

    /**
     * @return HasMany<BookingService, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    /**
     * @return HasMany<BookingStatusLog, $this>
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    /**
     * @return HasOne<ServiceOrder, $this>
     */
    public function serviceOrder(): HasOne
    {
        return $this->hasOne(ServiceOrder::class);
    }

    /**
     * Scope the query to bookings owned by the given customer.
     */
    public function scopeForCustomer(Builder $query, User $customer): Builder
    {
        return $query->where('user_id', $customer->getKey());
    }

    /**
     * Scope the query to bookings that still hold a slot.
     */
    public function scopeHoldingSlot(Builder $query): Builder
    {
        return $query->whereIn('status', array_map(
            fn (BookingStatus $status): string => $status->value,
            BookingStatus::slotHoldingCases(),
        ));
    }

    public function scheduledAtLocal(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->scheduled_at)
            ->timezone(config('app.display_timezone'));
    }

    public function canBeChanged(): bool
    {
        return $this->status->isOpen();
    }

    public function totalPrice(): float
    {
        return (float) $this->services->sum(fn (BookingService $service): float => (float) $service->price_snapshot);
    }

    public function totalDurationMinutes(): int
    {
        return (int) $this->services->sum(
            fn (BookingService $service): int => (int) ($service->duration_snapshot_minutes ?? 0),
        );
    }

    public function formattedTotalPrice(): string
    {
        return 'Rp '.number_format($this->totalPrice(), 0, ',', '.');
    }

    public function formattedTotalDuration(): string
    {
        $minutes = $this->totalDurationMinutes();

        return $minutes > 0 ? $minutes.' menit' : 'Tanpa estimasi';
    }
}
