<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Admin = 'admin';
    case Mechanic = 'mechanic';
    case InventoryStaff = 'inventory_staff';
    case Cashier = 'cashier';
    case Owner = 'owner';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Pelanggan',
            self::Admin => 'Admin',
            self::Mechanic => 'Mekanik',
            self::InventoryStaff => 'Staf Inventaris',
            self::Cashier => 'Kasir',
            self::Owner => 'Pemilik',
        };
    }

    public function isStaff(): bool
    {
        return $this !== self::Customer;
    }

    /**
     * @return list<string>
     */
    public static function staffValues(): array
    {
        return array_values(array_map(
            fn (self $role): string => $role->value,
            array_filter(self::cases(), fn (self $role): bool => $role->isStaff()),
        ));
    }
}
