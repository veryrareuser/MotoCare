<?php

namespace App\Enums;

enum NotificationKind: string
{
    case Reminder = 'reminder';
    case LowStock = 'low_stock';

    public function label(): string
    {
        return match ($this) {
            self::Reminder => 'Pengingat servis',
            self::LowStock => 'Stok rendah',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Reminder => 'bell',
            self::LowStock => 'alert',
        };
    }

    /**
     * Badge classes matching the shared design language.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Reminder => 'bg-warm text-[#8A5A00]',
            self::LowStock => 'bg-red-50 text-red-600',
        };
    }
}
