<?php

namespace App\Models;

use Database\Factories\BookingServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'service_type_id', 'price_snapshot', 'duration_snapshot_minutes'])]
class BookingService extends Model
{
    /** @use HasFactory<BookingServiceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_snapshot' => 'decimal:2',
            'duration_snapshot_minutes' => 'integer',
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
     * @return BelongsTo<ServiceType, $this>
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class)->withTrashed();
    }

    public function formattedPrice(): string
    {
        return 'Rp '.number_format((float) $this->price_snapshot, 0, ',', '.');
    }

    public function formattedDuration(): string
    {
        return $this->duration_snapshot_minutes === null
            ? 'Tanpa estimasi'
            : $this->duration_snapshot_minutes.' menit';
    }
}
