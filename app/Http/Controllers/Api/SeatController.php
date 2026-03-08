<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Services\SeatLockService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for managing seat locks and availability.
 *
 * Endpoints:
 * - GET /api/seats - Get seat statuses for a show
 * - POST /api/lock-seat - Lock a seat
 * - POST /api/release-seat - Release a seat lock
 * - POST /api/release-all-seats - Release all locks for a session
 * - GET /api/seat-layout - Get seat layout configuration
 */
class SeatController extends Controller
{
    protected SeatLockService $seatLockService;

    public function __construct(SeatLockService $seatLockService)
    {
        $this->seatLockService = $seatLockService;
    }

    /**
     * Get the status of all seats for a specific movie show.
     *
     * Query Parameters:
     * - movie_id: int (required)
     * - show_date: string YYYY-MM-DD (required)
     * - show_time: string HH:MM (required)
     *
     * Response:
     * {
     *   "success": true,
     *   "locked": [
     *     {
     *       "seat_number": "A1",
     *       "session_id": "session_id",
     *       "remaining_seconds": 300,
     *       "expires_at": "2026-03-07T14:34:15Z"
     *     }
     *   ],
     *   "booked": ["A2", "A3"]
     * }
     */
    public function getSeats(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'movie_id' => 'required|integer|exists:movies,id',
                'show_date' => 'required|date_format:Y-m-d',
                'show_time' => 'required|date_format:H:i',
            ]);

            $statuses = $this->seatLockService->getSeatStatuses(
                $validated['movie_id'],
                $validated['show_date'],
                $validated['show_time']
            );

            return response()->json([
                'success' => true,
                'locked' => $statuses['locked'],
                'booked' => $statuses['booked'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch seat statuses', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch seat statuses.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lock a seat for a specific session.
     *
     * Request Body:
     * {
     *   "movie_id": int,
     *   "show_date": "YYYY-MM-DD",
     *   "show_time": "HH:MM",
     *   "seat_number": "A1",
     *   "session_id": "unique_user_identifier"
     * }
     *
     * Response:
     * {
     *   "success": true,
     *   "message": "Seat locked successfully.",
     *   "lock": {
     *     "id": 1,
     *     "seat_number": "A1",
     *     "locked_at": "2026-03-07T14:30:00Z",
     *     "expires_at": "2026-03-07T14:35:00Z"
     *   },
     *   "remaining_seconds": 300
     * }
     */
    public function lockSeat(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'movie_id' => 'required|integer|exists:movies,id',
                'show_date' => 'required|date_format:Y-m-d',
                'show_time' => 'required|date_format:H:i',
                'seat_number' => 'required|string|regex:/^[A-Z]\d{1,2}$/',
                'session_id' => 'required|string|min:10',
            ]);

            $result = $this->seatLockService->lockSeat(
                $validated['movie_id'],
                $validated['show_date'],
                $validated['show_time'],
                $validated['seat_number'],
                $validated['session_id']
            );

            return response()->json($result, $result['success'] ? 200 : 409);
        } catch (\Exception $e) {
            Log::error('Failed to lock seat', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to lock seat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Release a seat lock manually.
     * Called when user deselects a seat or navigates away.
     *
     * Request Body:
     * {
     *   "movie_id": int,
     *   "show_date": "YYYY-MM-DD",
     *   "show_time": "HH:MM",
     *   "seat_number": "A1",
     *   "session_id": "unique_user_identifier"
     * }
     */
    public function releaseSeat(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'movie_id' => 'required|integer|exists:movies,id',
                'show_date' => 'required|date_format:Y-m-d',
                'show_time' => 'required|date_format:H:i',
                'seat_number' => 'required|string|regex:/^[A-Z]\d{1,2}$/i',
                'session_id' => 'required|string|min:10',
            ]);

            $result = $this->seatLockService->releaseSeat(
                $validated['movie_id'],
                $validated['show_date'],
                $validated['show_time'],
                $validated['seat_number'],
                $validated['session_id']
            );

            return response()->json($result, $result['success'] ? 200 : 404);
        } catch (\Exception $e) {
            Log::error('Failed to release seat', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to release seat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Release all seat locks for a specific session.
     * Called when user exits the booking flow.
     *
     * Request Body:
     * {
     *   "session_id": "unique_user_identifier"
     * }
     */
    public function releaseAllSeats(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|string|min:10',
            ]);

            $releasedCount = $this->seatLockService->releaseAllSessionLocks(
                $validated['session_id']
            );

            return response()->json([
                'success' => true,
                'message' => "Released {$releasedCount} seat lock(s).",
                'released_count' => $releasedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to release all seats', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to release seats.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a specific seat is available.
     * Used for real-time validation before locking.
     *
     * Query Parameters:
     * - movie_id: int
     * - show_date: string YYYY-MM-DD
     * - show_time: string HH:MM
     * - seat_number: string (e.g., "A1")
     */
    public function checkSeatAvailability(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'movie_id' => 'required|integer|exists:movies,id',
                'show_date' => 'required|date_format:Y-m-d',
                'show_time' => 'required|date_format:H:i',
                'seat_number' => 'required|string|regex:/^[A-H]\d{1,2}$/i',
            ]);

            $isAvailable = $this->seatLockService->isSeatAvailable(
                $validated['movie_id'],
                $validated['show_date'],
                $validated['show_time'],
                $validated['seat_number']
            );

            return response()->json([
                'success' => true,
                'available' => $isAvailable,
                'seat_number' => $validated['seat_number'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check seat availability', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check seat availability.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
