<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayHere Checkout
    |--------------------------------------------------------------------------
    |
    | Sandbox payments are simulated. The merchant secret is used only on the
    | server to sign the checkout request and to verify the notify callback.
    |
    */

    'merchant_id' => env('PAYHERE_MERCHANT_ID', ''),

    'merchant_secret' => env('PAYHERE_MERCHANT_SECRET', ''),

    'sandbox' => filter_var(env('PAYHERE_SANDBOX', true), FILTER_VALIDATE_BOOLEAN),

    'currency' => 'LKR',

    'city' => env('PAYHERE_CITY', 'Monaragala'),

    /*
    | Pending online bookings keep their seats for this many minutes so a
    | customer can finish PayHere without someone else taking the same seats.
    |
    */
    'pending_hold_minutes' => (int) env('PAYHERE_PENDING_HOLD_MINUTES', 30),
];
