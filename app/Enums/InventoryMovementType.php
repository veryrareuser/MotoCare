<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Stok Masuk',
            self::Out => 'Stok Keluar',
            self::Adjustment => 'Penyesuaian',
        };
    }

    /**
     * Movement types staff may record from the stock screen.
     *
     * Stock-out is posted by service order part usage, not manually.
     *
     * @return list<self>
     */
    public static function manualCases(): array
    {
        return [self::In, self::Adjustment];
    }
}
