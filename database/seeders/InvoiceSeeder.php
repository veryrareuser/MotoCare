<?php

namespace Database\Seeders;

use App\Actions\Billing\CreateInvoiceForServiceOrder;
use App\Actions\Billing\RecordInvoicePayment;
use App\Actions\Billing\SetInvoiceDeposit;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Give every demo work order an invoice and show one deposit payment.
     */
    public function run(
        CreateInvoiceForServiceOrder $createInvoiceForServiceOrder,
        SetInvoiceDeposit $setInvoiceDeposit,
        RecordInvoicePayment $recordInvoicePayment,
    ): void {
        $cashier = User::query()->where('email', 'cashier@motocare.test')->first();

        if (! $cashier instanceof User) {
            return;
        }

        $serviceOrders = ServiceOrder::query()->orderBy('id')->get();

        foreach ($serviceOrders as $serviceOrder) {
            if (! $serviceOrder->invoice()->exists()) {
                $createInvoiceForServiceOrder->handle($serviceOrder);
            }
        }

        if (Payment::query()->exists()) {
            return;
        }

        $firstOrder = $serviceOrders->first();
        $invoice = $firstOrder?->invoice()->first();

        if (! $invoice instanceof Invoice || ! $invoice->isDraft()) {
            return;
        }

        $invoice = $setInvoiceDeposit->handle($invoice, true);

        if ((float) $invoice->deposit_amount <= 0) {
            return;
        }

        $recordInvoicePayment->handle(
            $invoice,
            (float) $invoice->deposit_amount,
            PaymentMethod::Cash,
            $cashier,
            'Deposit servis 50%.',
        );
    }
}
