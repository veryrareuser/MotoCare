<?php

namespace App\Actions\Billing;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetInvoiceDeposit
{
    /**
     * Enable or disable the optional deposit on a draft invoice.
     *
     * The percentage defaults to the configured 50% and the resulting amount
     * is stored as a snapshot of the current invoice total.
     *
     * @throws ValidationException
     */
    public function handle(Invoice $invoice, bool $required, ?float $percentage = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $required, $percentage): Invoice {
            $locked = Invoice::query()
                ->whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isDraft()) {
                throw ValidationException::withMessages([
                    'deposit_required' => 'Deposit hanya dapat diubah saat invoice masih draft.',
                ]);
            }

            if ($required) {
                $percentage ??= (float) config('invoice.deposit_percentage');

                if ($percentage < 1 || $percentage > 100) {
                    throw ValidationException::withMessages([
                        'deposit_percentage' => 'Persentase deposit harus antara 1 dan 100.',
                    ]);
                }
            } else {
                $percentage = null;
            }

            $locked->update([
                'deposit_required' => $required,
                'deposit_percentage' => $percentage,
                'deposit_amount' => $required
                    ? round((float) $locked->total_amount * $percentage / 100, 2)
                    : null,
            ]);

            return $locked;
        });
    }
}
