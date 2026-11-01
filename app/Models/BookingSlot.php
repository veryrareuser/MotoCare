<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\BookingSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slot_date', 'start_time', 'end_time', 'capacity', 'booked_count', 'is_active'])]
class BookingSlot extends Model
{
    /** @use HasFactory<BookingSlotFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot_date' => 'date',
            'capacity' => 'integer',
            'booked_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope the query to slots that can still accept bookings.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereColumn('booked_count', '<', 'capacity');
    }

    /**
     * Slot start in the workshop display timezone.
     */
    public function localStart(): CarbonImmutable
    {
        return CarbonImmutable::parse(
            $this->slot_date->toDateString().' '.$this->start_time,
            config('app.display_timezone'),
        );
    }

    /**
     * Slot end in the workshop display timezone.
     */
    public function localEnd(): CarbonImmutable
    {
        return CarbonImmutable::parse(
            $this->slot_date->toDateString().' '.$this->end_time,
            config('app.display_timezone'),
        );
    }

    /**
     * Slot start stored as UTC, matching the database convention.
     */
    public function scheduledAt(): CarbonImmutable
    {
        return $this->localStart()->utc();
    }

    public function remainingCapacity(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }

    public function isFull(): bool
    {
        return $this->booked_count >= $this->capacity;
    }

    public function isInThePast(): bool
    {
        return $this->localStart()->lessThanOrEqualTo(CarbonImmutable::now(config('app.display_timezone')));
    }

    public function displayRange(): string
    {
        return $this->localStart()->format('H:i').' - '.$this->localEnd()->format('H:i');
    }

    public function displayDate(): string
    {
        return $this->localStart()->translatedFormat('l, d M Y');
    }
}
