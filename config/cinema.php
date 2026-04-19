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

];
