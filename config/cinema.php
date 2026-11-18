<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Seat Locking Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the seat locking system used during the booking flow.
    | Seats are temporarily locked to prevent race conditions while users
    | complete their booking.
    |
    */

    'seat_lock_duration_minutes' => (int) env('SEAT_LOCK_DURATION_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | Public business details
    |--------------------------------------------------------------------------
    |
    | Shown on the website footer, contact page, and policy pages.
    |
    */

    'business' => [
        'name' => env('CINEMA_BUSINESS_NAME', 'H H P Cinema'),
        'address' => env('CINEMA_ADDRESS', 'No 73, Pothuvil Road, Monaragala, Sri Lanka'),
        'phone' => env('CINEMA_PHONE', '+94 77 179 8183'),
        'email' => env('CINEMA_EMAIL', 'kiimalka@gmail.com'),
        'country' => env('CINEMA_COUNTRY', 'Sri Lanka'),
        'policies_updated' => env('CINEMA_POLICIES_UPDATED', '18 November 2026'),
    ],

];
