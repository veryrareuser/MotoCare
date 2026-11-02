<?php

namespace App\Actions\ServiceOrder;

use App\Models\DailySequence;
use Carbon\CarbonImmutable;

class NextWorkOrderNumber
{
    /**
     * Issue the next daily work order number.
     *
     * The sequence row is locked so concurrent check-ins cannot receive the
     * same number. The unique index on service_orders is the final guard.
     *
     * @return array{date: string, number: string, sequence: int}
     */
    public function handle(?CarbonImmutable $date = null): array
    {
        $date ??= CarbonImmutable::now(config('app.display_timezone'));
        $dateString = $date->toDateString();
        $timestamp = now();

        DailySequence::query()->insertOrIgnore([
            'name' => 'work_order',
            'date' => $dateString,
            'last_number' => 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $sequence = DailySequence::query()
            ->where('name', 'work_order')
            ->where('date', $dateString)
            ->lockForUpdate()
            ->firstOrFail();

        $next = $sequence->last_number + 1;
        $sequence->update(['last_number' => $next]);

        return [
            'date' => $dateString,
            'number' => config('service_order.work_order_prefix')
                .'-'.$date->format('Ymd')
                .'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT),
            'sequence' => $next,
        ];
    }
}
