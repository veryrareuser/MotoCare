<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingStatusLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'changed_by',
    'old_status',
    'new_status',
    'old_scheduled_at',
    'new_scheduled_at',
    'reason',
])]
class BookingStatusLog extends Model
{
    /** @use HasFactory<BookingStatusLogFactory> */
    use HasFactory;

    /**
     * History rows are immutable; only the creation time is recorded.
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
            'old_status' => BookingStatus::class,
            'new_status' => BookingStatus::class,
            'old_scheduled_at' => 'datetime',
            'new_scheduled_at' => 'datetime',
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
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Short description of what changed, used in the booking timeline.
     */
    public function summary(): string
    {
        if ($this->old_scheduled_at !== null && $this->new_scheduled_at !== null) {
            return 'Jadwal diubah dari '
                .$this->old_scheduled_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i')
                .' ke '
                .$this->new_scheduled_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i').'.';
        }

        if ($this->old_status === null) {
            return 'Booking dikonfirmasi pada '
                .$this->new_scheduled_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y H:i').'.';
        }

        return 'Status berubah dari '.$this->old_status->label().' menjadi '.$this->new_status->label().'.';
    }
}
