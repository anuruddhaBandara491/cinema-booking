<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Seat;
use App\Models\SeatLock;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing seat locks and bookings.
 * Handles the core logic for seat availability, locking, and booking.
 */
class SeatLockService
{
    /**
     * Duration of a seat lock in minutes.
     * Configurable via SEAT_LOCK_DURATION_MINUTES in .env
     */
    public function getLockDurationMinutes()
    {
        return (int) config('cinema.seat_lock_duration_minutes', 5);
    }

    /**
     * Get the status of all seats for a specific movie show.
     *
     * Returns an array with:
     * - 'locked': Temporarily locked by users (with remaining time)
     * - 'booked': Permanently booked (from bookings table)
     *
     * @param int $movieId
     * @param string $showDate (format: YYYY-MM-DD)
     * @param string $showTime (format: HH:MM)
     * @return array ['locked' => [...], 'booked' => [...]]
     */
    public function getSeatStatuses($movieId, $showDate, $showTime)
    {
        // Get all active locks for this show
        $activeLocks = SeatLock::forShow($movieId, $showDate, $showTime)
            ->active()
            ->get()
            ->map(fn($lock) => [
                'seat_number' => $lock->seat_number,
                'session_id' => $lock->session_id,
                'remaining_seconds' => $lock->getRemainingSeconds(),
                'expires_at' => $lock->expires_at->toIso8601String(),
            ])
            ->values();

        // Get all booked seats for this show (from completed bookings)
        $bookedSeats = Booking::where('movie_id', $movieId)
            ->where('booking_date', $showDate)
            ->where('booking_time', $showTime)
            ->where('payment_status', 'completed')
            ->get()
            ->flatMap(fn($booking) => $booking->selected_seats ?? [])
            ->unique()
            ->values();

        return [
            'locked' => $activeLocks->toArray(),
            'booked' => $bookedSeats->toArray(),
        ];
    }

    /**
     * Lock a seat for a specific session.
     *
     * This method attempts to lock a seat with concurrency protection.
     * Uses database transactions and unique constraints to prevent race conditions.
     *
     * @param int $movieId
     * @param string $showDate (format: YYYY-MM-DD)
     * @param string $showTime (format: HH:MM)
     * @param string $seatNumber (e.g., "A1")
     * @param string $sessionId (unique user identifier)
     * @return array ['success' => bool, 'message' => string, 'lock' => SeatLock|null, 'remaining_seconds' => int|null]
     */
    public function lockSeat($movieId, $showDate, $showTime, $seatNumber, $sessionId)
    {
        try {
            return DB::transaction(function () use ($movieId, $showDate, $showTime, $seatNumber, $sessionId) {
                // 1. Check if seat is already booked (completed bookings only)
                $isBooked = Booking::where('movie_id', $movieId)
                    ->where('booking_date', $showDate)
                    ->where('booking_time', $showTime)
                    ->where('payment_status', 'completed')
                    ->get()
                    ->flatMap(fn($booking) => $booking->selected_seats ?? [])
                    ->contains($seatNumber);

                if ($isBooked) {
                    return [
                        'success' => false,
                        'message' => 'This seat is already booked.',
                        'status' => 'booked',
                    ];
                }

                // 2. Check for existing active lock on this seat
                $existingLock = SeatLock::forShow($movieId, $showDate, $showTime)
                    ->where('seat_number', $seatNumber)
                    ->active()
                    ->first();

                if ($existingLock) {
                    // Seat is locked by someone else
                    if ($existingLock->session_id !== $sessionId) {
                        return [
                            'success' => false,
                            'message' => 'This seat is currently locked by another user.',
                            'status' => 'locked',
                            'remaining_seconds' => $existingLock->getRemainingSeconds(),
                        ];
                    }

                    // Seat is already locked by this user - refresh the lock
                    $lockDuration = $this->getLockDurationMinutes();
                    $existingLock->update([
                        'locked_at' => now(),
                        'expires_at' => now()->addMinutes($lockDuration),
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Seat lock refreshed.',
                        'lock' => $existingLock,
                        'remaining_seconds' => $lockDuration * 60,
                    ];
                }

                // 3. Create a new lock for this seat
                $lockDuration = $this->getLockDurationMinutes();
                $lock = SeatLock::create([
                    'movie_id' => $movieId,
                    'show_date' => $showDate,
                    'show_time' => $showTime,
                    'seat_number' => $seatNumber,
                    'session_id' => $sessionId,
                    'locked_at' => now(),
                    'expires_at' => now()->addMinutes($lockDuration),
                ]);

                return [
                    'success' => true,
                    'message' => 'Seat locked successfully.',
                    'lock' => $lock,
                    'remaining_seconds' => $lockDuration * 60,
                ];
            });
        } catch (QueryException $e) {
            // Unique constraint violation - seat was locked between check and insert
            $existingLock = SeatLock::forShow($movieId, $showDate, $showTime)
                ->where('seat_number', $seatNumber)
                ->active()
                ->first();

            if ($existingLock && $existingLock->session_id === $sessionId) {
                return [
                    'success' => true,
                    'message' => 'Seat is already locked by you.',
                    'lock' => $existingLock,
                    'remaining_seconds' => $existingLock->getRemainingSeconds(),
                ];
            }

            return [
                'success' => false,
                'message' => 'This seat is currently locked by another user. Please try another seat.',
                'status' => 'locked',
            ];
        }
    }

    /**
     * Release a seat lock manually.
     * Typically called when user navigates away or deselects a seat.
     *
     * @param int $movieId
     * @param string $showDate
     * @param string $showTime
     * @param string $seatNumber
     * @param string $sessionId
     * @return array ['success' => bool, 'message' => string]
     */
    public function releaseSeat($movieId, $showDate, $showTime, $seatNumber, $sessionId)
    {
        $lock = SeatLock::forShow($movieId, $showDate, $showTime)
            ->where('seat_number', $seatNumber)
            ->where('session_id', $sessionId)
            ->active()
            ->first();

        if (!$lock) {
            return [
                'success' => false,
                'message' => 'No active lock found for this seat.',
            ];
        }

        $lock->delete();

        return [
            'success' => true,
            'message' => 'Seat lock released.',
        ];
    }

    /**
     * Release all locks for a specific session.
     * Useful when user exits the booking flow.
     *
     * @param string $sessionId
     * @return int Number of locks released
     */
    public function releaseAllSessionLocks($sessionId)
    {
        return SeatLock::where('session_id', $sessionId)
            ->get()
            ->each(fn($lock) => $lock->delete());
        // Alternatively, we can do a bulk delete if we don't need to trigger model events:
        // return SeatLock::where('session_id', $sessionId)
        //     ->delete();
    }

    /**
     * Release all expired locks.
     * Should be called periodically via a scheduled command.
     *
     * @return int Number of locks released
     */
    public function cleanupExpiredLocks()
    {
        return SeatLock::where('expires_at', '<=', now())->delete();
    }



    /**
     * Check if a specific seat is available (not booked and not locked).
     *
     * @param int $movieId
     * @param string $showDate
     * @param string $showTime
     * @param string $seatNumber
     * @return bool
     */
    public function isSeatAvailable($movieId, $showDate, $showTime, $seatNumber)
    {
        // Check if booked (completed bookings only)
        $isBooked = Booking::where('movie_id', $movieId)
            ->where('booking_date', $showDate)
            ->where('booking_time', $showTime)
            ->where('payment_status', 'completed')
            ->get()
            ->flatMap(fn($booking) => $booking->selected_seats ?? [])
            ->contains($seatNumber);

        if ($isBooked) {
            return false;
        }

        // Check if locked
        $isLocked = SeatLock::forShow($movieId, $showDate, $showTime)
            ->where('seat_number', $seatNumber)
            ->active()
            ->exists();

        return !$isLocked;
    }
}

