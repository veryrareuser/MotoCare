<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case ConvertedToService = 'converted_to_service';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Confirmed => 'Terkonfirmasi',
            self::CheckedIn => 'Sudah check-in',
            self::ConvertedToService => 'Menjadi servis',
            self::Cancelled => 'Dibatalkan',
            self::NoShow => 'Tidak hadir',
        };
    }

    /**
     * Badge classes matching the shared design language.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Confirmed => 'bg-brand-soft text-brand-dark',
            self::CheckedIn => 'bg-warm text-[#8A5A00]',
            self::ConvertedToService => 'bg-canvas text-ink/70',
            self::Cancelled => 'bg-red-50 text-red-600',
            self::NoShow => 'bg-canvas text-muted',
        };
    }

    /**
     * Whether the booking is still an upcoming appointment that may be changed.
     */
    public function isOpen(): bool
    {
        return $this === self::Confirmed;
    }

    /**
     * Whether the booking can no longer be rescheduled or cancelled.
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::ConvertedToService, self::Cancelled, self::NoShow], strict: true);
    }

    /**
     * Statuses that occupy a slot.
     *
     * @return list<self>
     */
    public static function slotHoldingCases(): array
    {
        return [self::Confirmed, self::CheckedIn, self::ConvertedToService];
    }
}
