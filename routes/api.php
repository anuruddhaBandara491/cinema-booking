<?php

use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\BookingFlowLogApiController;
use Illuminate\Support\Facades\Route;

/**
 * API Routes for Seat Locking System and Booking Flow Logging
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

    // Booking flow logging operations
    Route::post('/log/user-details', [BookingFlowLogApiController::class, 'logUserDetails']);
    Route::post('/log/movie-selection', [BookingFlowLogApiController::class, 'logMovieSelection']);
    Route::post('/log/ticket-count', [BookingFlowLogApiController::class, 'logTicketCount']);
    Route::post('/log/seat-selection', [BookingFlowLogApiController::class, 'logSeatSelection']);
    Route::post('/log/payment-attempt', [BookingFlowLogApiController::class, 'logPaymentAttempt']);
    Route::post('/log/payment-failure', [BookingFlowLogApiController::class, 'logPaymentFailure']);
});
