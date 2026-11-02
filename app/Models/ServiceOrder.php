<?php

namespace App\Models;

use App\Enums\ServiceOrderStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ServiceOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'work_order_number',
    'booking_id',
    'motorcycle_id',
    'customer_id',
    'odometer_in',
    'odometer_out',
    'status',
    'diagnosis',
    'work_notes',
    'checked_in_at',
    'started_at',
    'completed_at',
    'picked_up_at',
])]
class ServiceOrder extends Model
{
    /** @use HasFactory<ServiceOrderFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'odometer_in' => 'integer',
            'odometer_out' => 'integer',
            'status' => ServiceOrderStatus::class,
            'checked_in_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'picked_up_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id')->withTrashed();
    }

    /**
     * @return BelongsTo<Motorcycle, $this>
     */
    public function motorcycle(): BelongsTo
    {
        return $this->belongsTo(Motorcycle::class)->withTrashed();
    }

    /**
     * @return HasMany<ServiceOrderMechanic, $this>
     */
    public function mechanicAssignments(): HasMany
    {
        return $this->hasMany(ServiceOrderMechanic::class);
    }

    /**
     * @return HasMany<ServiceOrderMechanic, $this>
     */
    public function activeMechanicAssignments(): HasMany
    {
        return $this->mechanicAssignments()->whereNull('unassigned_at');
    }

    /**
     * @return HasMany<ServiceOrderService, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(ServiceOrderService::class);
    }

    /**
     * @return HasMany<ServiceOrderPart, $this>
     */
    public function parts(): HasMany
    {
        return $this->hasMany(ServiceOrderPart::class);
    }

    /**
     * @return HasMany<ServiceStatusLog, $this>
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ServiceStatusLog::class);
    }

    /**
     * Scope the query to work orders checked in on the given local date.
     */
    public function scopeForDate(Builder $query, CarbonImmutable $date): Builder
    {
        $start = $date->startOfDay()->utc();
        $end = $date->addDay()->startOfDay()->utc();

        return $query->where('checked_in_at', '>=', $start)
            ->where('checked_in_at', '<', $end);
    }

    /**
     * Scope the query to work orders that still need workshop attention.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', array_map(
            fn (ServiceOrderStatus $status): string => $status->value,
            ServiceOrderStatus::activeCases(),
        ));
    }

    public function isLocked(): bool
    {
        return $this->status->isLocked();
    }

    public function queueLabel(): string
    {
        return $this->booking_id === null
            ? 'Walk-in'
            : $this->booking->queue_number;
    }

    public function checkedInAtLocal(): CarbonImmutable
    {
        return $this->checked_in_at->toImmutable()->timezone(config('app.display_timezone'));
    }

    public function totalServiceAmount(): float
    {
        return (float) $this->services->sum(
            fn (ServiceOrderService $service): float => $service->lineTotal(),
        );
    }

    public function totalPartAmount(): float
    {
        return (float) $this->parts->sum(
            fn (ServiceOrderPart $part): float => $part->lineTotal(),
        );
    }

    public function totalAmount(): float
    {
        return $this->totalServiceAmount() + $this->totalPartAmount();
    }

    public function formattedServiceAmount(): string
    {
        return 'Rp '.number_format($this->totalServiceAmount(), 0, ',', '.');
    }

    public function formattedPartAmount(): string
    {
        return 'Rp '.number_format($this->totalPartAmount(), 0, ',', '.');
    }

    public function formattedTotalAmount(): string
    {
        return 'Rp '.number_format($this->totalAmount(), 0, ',', '.');
    }

    public function formattedOdometerIn(): string
    {
        return number_format($this->odometer_in, 0, ',', '.').' km';
    }

    public function formattedOdometerOut(): string
    {
        return $this->odometer_out === null
            ? '—'
            : number_format($this->odometer_out, 0, ',', '.').' km';
    }

    /**
     * Active mechanic names joined for compact table display.
     */
    public function mechanicNames(): string
    {
        return $this->activeMechanicAssignments
            ->map(fn (ServiceOrderMechanic $assignment): ?string => $assignment->mechanic?->name)
            ->filter()
            ->implode(', ');
    }
}
