<?php

namespace App\Enums;

enum MaintenanceReminderStatus: string
{
    case Scheduled = 'scheduled';
    case Sent = 'sent';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::Sent => 'Terkirim',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Badge classes matching the shared design language.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-warm text-[#8A5A00]',
            self::Sent => 'bg-brand-soft text-brand-dark',
            self::Completed => 'bg-brand text-ink',
            self::Cancelled => 'bg-canvas text-muted',
        };
    }

    /**
     * Whether the reminder still needs attention from the workshop.
     */
    public function isOpen(): bool
    {
        return in_array($this, [self::Scheduled, self::Sent], strict: true);
    }
}
