<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Invoice and Payment Numbering
    |--------------------------------------------------------------------------
    |
    | Invoices and payments receive daily sequence numbers in the form
    | INV-YYYYMMDD-NNN and PAY-YYYYMMDD-NNN. The prefixes are configurable
    | so printed documents can follow the workshop's own format.
    |
    */

    'invoice_prefix' => 'INV',
    'payment_prefix' => 'PAY',

    /*
    |--------------------------------------------------------------------------
    | Deposit Defaults
    |--------------------------------------------------------------------------
    |
    | A deposit is optional per invoice. When the cashier enables it, the
    | percentage defaults to 50% and is stored as an invoice snapshot.
    |
    */

    'deposit_percentage' => 50,

];
