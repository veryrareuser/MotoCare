<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Dibayar',
            self::PartiallyPaid => 'Dibayar Sebagian',
            self::Paid => 'Lunas',
        };
    }

    /**
     * Badge classes matching the shared design language.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-warm text-[#8A5A00]',
            self::PartiallyPaid => 'bg-brand-soft text-brand-dark',
            self::Paid => 'bg-brand text-ink',
        };
    }

    /**
     * Resolve the status implied by the amounts recorded on an invoice.
     */
    public static function forAmounts(float $paidAmount, float $totalAmount): self
    {
        $paid = round($paidAmount, 2);
        $total = round($totalAmount, 2);

        if ($paid <= 0.0) {
            return self::Unpaid;
        }

        return $paid >= $total ? self::Paid : self::PartiallyPaid;
    }
}
