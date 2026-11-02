<?php

namespace App\Actions\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordInvoicePayment
{
    public function __construct(
        private NextPaymentNumber $nextPaymentNumber,
    ) {}

    /**
     * Record a manual payment and recalculate the invoice balance atomically.
     *
     * @throws ValidationException
     */
    public function handle(
        Invoice $invoice,
        float $amount,
        PaymentMethod $method,
        User $actor,
        ?string $notes = null,
    ): Payment {
        return DB::transaction(function () use ($invoice, $amount, $method, $actor, $notes): Payment {
            $locked = Invoice::query()
                ->whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $amount = round($amount, 2);
            $remaining = round((float) $locked->remaining_amount, 2);

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah pembayaran harus lebih dari nol.',
                ]);
            }

            if ($remaining <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Invoice ini sudah lunas.',
                ]);
            }

            if ($amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah pembayaran melebihi sisa tagihan (Rp '
                        .number_format($remaining, 0, ',', '.').').',
                ]);
            }

            $number = $this->nextPaymentNumber->handle();

            $payment = $locked->payments()->create([
                'payment_reference' => $number['number'],
                'amount' => $amount,
                'payment_method' => $method,
                'payment_status' => PaymentStatus::Paid,
                'received_by' => $actor->getKey(),
                'paid_at' => now(),
                'notes' => $notes,
            ]);

            $paid = round((float) $locked->paid_amount + $amount, 2);
            $total = round((float) $locked->total_amount, 2);

            $locked->update([
                'paid_amount' => $paid,
                'remaining_amount' => round($total - $paid, 2),
                'payment_status' => InvoiceStatus::forAmounts($paid, $total),
            ]);

            return $payment->load('invoice');
        });
    }
}
