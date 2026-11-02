<?php

namespace App\Actions\Billing;

use App\Models\DailySequence;
use Carbon\CarbonImmutable;

class NextInvoiceNumber
{
    /**
     * Issue the next daily invoice number.
     *
     * @return array{date: string, number: string, sequence: int}
     */
    public function handle(?CarbonImmutable $date = null): array
    {
        $date ??= CarbonImmutable::now(config('app.display_timezone'));
        $dateString = $date->toDateString();
        $timestamp = now();

        DailySequence::query()->insertOrIgnore([
            'name' => 'invoice',
            'date' => $dateString,
            'last_number' => 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $sequence = DailySequence::query()
            ->where('name', 'invoice')
            ->where('date', $dateString)
            ->lockForUpdate()
            ->firstOrFail();

        $next = $sequence->last_number + 1;
        $sequence->update(['last_number' => $next]);

        return [
            'date' => $dateString,
            'number' => config('invoice.invoice_prefix')
                .'-'.$date->format('Ymd')
                .'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT),
            'sequence' => $next,
        ];
    }
}
