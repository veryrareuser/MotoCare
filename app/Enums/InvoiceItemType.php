<?php

namespace App\Enums;

enum InvoiceItemType: string
{
    case Service = 'service';
    case Sparepart = 'sparepart';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Service => 'Layanan',
            self::Sparepart => 'Sparepart',
            self::Other => 'Lainnya',
        };
    }
}
