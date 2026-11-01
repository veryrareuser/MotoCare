<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Queue Number
    |--------------------------------------------------------------------------
    |
    | Daily queue numbers are issued when a booking is confirmed, using the
    | format "{prefix}-{sequence}" (for example "A-024"). The sequence resets
    | every queue date and is unique per day.
    |
    */

    'queue_prefix' => 'A',

    /*
    |--------------------------------------------------------------------------
    | Booking Window
    |--------------------------------------------------------------------------
    |
    | How many days ahead of today customers may book a slot. Today counts as
    | day zero, so a value of 14 allows booking through the next 14 days.
    |
    */

    'booking_window_days' => 14,

];
