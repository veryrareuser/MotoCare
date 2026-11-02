<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Throwable;

abstract class Controller
{
    /**
     * Resolve a Y-m-d query value in the display timezone.
     *
     * Invalid or missing values fall back to today so list screens always
     * have a usable date filter.
     */
    protected function resolveDateFilter(mixed $value): CarbonImmutable
    {
        $today = CarbonImmutable::now(config('app.display_timezone'))->startOfDay();

        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return $today;
        }

        try {
            $date = CarbonImmutable::createFromFormat('Y-m-d', $value, config('app.display_timezone'));

            if ($date === null || $date->format('Y-m-d') !== $value) {
                return $today;
            }

            return $date->startOfDay();
        } catch (Throwable) {
            return $today;
        }
    }
}
