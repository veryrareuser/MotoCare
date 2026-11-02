<?php

namespace App\Actions\Billing;

use App\Models\DailySequence;
use Carbon\CarbonImmutable;

class NextPaymentNumber
{
    /**
     * Issue the next daily payment reference.
     *
     * @return array{date: string, number: string, sequence: int}
     */
    public function handle(?CarbonImmutable $date = null): array
    {
        $date ??= CarbonImmutable::now(config('app.display_timezone'));
        $dateString = $date->toDateString();
        $timestamp = now();

        DailySequence::query()->insertOrIgnore([
            'name' => 'payment',
            'date' => $dateString,
            'last_number' => 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $sequence = DailySequence::query()
            ->where('name', 'payment')
            ->where('date', $dateString)
            ->lockForUpdate()
            ->firstOrFail();

        $next = $sequence->last_number + 1;
        $sequence->update(['last_number' => $next]);

        return [
            'date' => $dateString,
            'number' => config('invoice.payment_prefix')
                .'-'.$date->format('Ymd')
                .'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT),
            'sequence' => $next,
        ];
    }
}
