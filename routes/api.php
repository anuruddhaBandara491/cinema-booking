<?php

use App\Http\Controllers\Api\SeatController;
use Illuminate\Support\Facades\Route;

/**
 * API Routes for Seat Locking System
 *
 * Prefix: /api
 * No authentication required (users can book without logging in)
 */

Route::middleware('api')->group(function () {
    // Seat operations
    Route::get('/seats', [SeatController::class, 'getSeats']);
    Route::get('/seat-availability', [SeatController::class, 'checkSeatAvailability']);

    // Seat locking operations
    Route::post('/lock-seat', [SeatController::class, 'lockSeat']);
    Route::post('/release-seat', [SeatController::class, 'releaseSeat']);
    Route::post('/release-all-seats', [SeatController::class, 'releaseAllSeats']);
});
