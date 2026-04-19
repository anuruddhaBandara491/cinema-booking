<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingFlowLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for booking flow logging.
 * Used by frontend to log booking actions.
 */
class BookingFlowLogApiController extends Controller
{
    protected BookingFlowLogService $flowLogService;

    public function __construct(BookingFlowLogService $flowLogService)
    {
        $this->flowLogService = $flowLogService;
    }

    /**
     * Log user details step (Step 1).
     */
    public function logUserDetails(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|string',
                'user_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            $log = $this->flowLogService->logUserDetails(
                sessionId: $validated['session_id'],
                userName: $validated['user_name'],
                phoneNumber: $validated['phone_number'],
                email: $validated['email'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'User details logged successfully',
                'log_id' => $log->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log user details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log movie selection step (Step 2).
     */
    public function logMovieSelection(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|string',
                'movie_id' => 'required|integer|exists:movies,id',
                'show_date' => 'required|date_format:Y-m-d',
                'show_time' => 'required|string|max:5',
                'user_name' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            $log = $this->flowLogService->logMovieSelection(
                sessionId: $validated['session_id'],
                movieId: $validated['movie_id'],
                showDate: $validated['show_date'],
                showTime: $validated['show_time'],
                userName: $validated['user_name'] ?? null,
                phoneNumber: $validated['phone_number'] ?? null,
                email: $validated['email'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Movie selection logged successfully',
                'log_id' => $log->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log movie selection: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log ticket count selection step (Step 3).
     */
    public function logTicketCount(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|string',
                'ticket_count' => 'required|integer|min:1',
                'movie_id' => 'nullable|integer|exists:movies,id',
                'show_date' => 'nullable|date_format:Y-m-d',
                'show_time' => 'nullable|string|max:5',
                'user_name' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            $log = $this->flowLogService->logTicketCount(
                sessionId: $validated['session_id'],
                ticketCount: $validated['ticket_count'],
                movieId: $validated['movie_id'] ?? null,
                showDate: $validated['show_date'] ?? null,
                showTime: $validated['show_time'] ?? null,
                userName: $validated['user_name'] ?? null,
                phoneNumber: $validated['phone_number'] ?? null,
                email: $validated['email'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Ticket count logged successfully',
                'log_id' => $log->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log ticket count: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log seat selection step (Step 4).
     */
    public function logSeatSelection(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|string',
                'selected_seats' => 'required|array|min:1',
                'selected_seats.*' => 'string',
                'movie_id' => 'nullable|integer|exists:movies,id',
                'show_date' => 'nullable|date_format:Y-m-d',
                'show_time' => 'nullable|string|max:5',
                'ticket_count' => 'nullable|integer|min:1',
                'user_name' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            $log = $this->flowLogService->logSeatSelection(
                sessionId: $validated['session_id'],
                selectedSeats: $validated['selected_seats'],
                movieId: $validated['movie_id'] ?? null,
                showDate: $validated['show_date'] ?? null,
                showTime: $validated['show_time'] ?? null,
                ticketCount: $validated['ticket_count'] ?? null,
                userName: $validated['user_name'] ?? null,
                phoneNumber: $validated['phone_number'] ?? null,
                email: $validated['email'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Seat selection logged successfully',
                'log_id' => $log->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log seat selection: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log payment attempt (before redirecting to gateway).
     */
    public function logPaymentAttempt(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|string',
                'movie_id' => 'nullable|integer|exists:movies,id',
                'show_date' => 'nullable|date_format:Y-m-d',
                'show_time' => 'nullable|string|max:5',
                'selected_seats' => 'nullable|array',
                'selected_seats.*' => 'string',
                'ticket_count' => 'nullable|integer|min:1',
                'user_name' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            $log = $this->flowLogService->logPaymentAttempt(
                sessionId: $validated['session_id'],
                movieId: $validated['movie_id'] ?? null,
                showDate: $validated['show_date'] ?? null,
                showTime: $validated['show_time'] ?? null,
                selectedSeats: $validated['selected_seats'] ?? null,
                ticketCount: $validated['ticket_count'] ?? null,
                userName: $validated['user_name'] ?? null,
                phoneNumber: $validated['phone_number'] ?? null,
                email: $validated['email'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment attempt logged successfully',
                'log_id' => $log->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log payment attempt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log payment failure.
     */
    public function logPaymentFailure(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|string',
                'error_message' => 'nullable|string',
                'movie_id' => 'nullable|integer|exists:movies,id',
                'show_date' => 'nullable|date_format:Y-m-d',
                'show_time' => 'nullable|string|max:5',
                'selected_seats' => 'nullable|array',
                'selected_seats.*' => 'string',
                'ticket_count' => 'nullable|integer|min:1',
                'user_name' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            $log = $this->flowLogService->logPaymentFailure(
                sessionId: $validated['session_id'],
                errorMessage: $validated['error_message'] ?? null,
                movieId: $validated['movie_id'] ?? null,
                showDate: $validated['show_date'] ?? null,
                showTime: $validated['show_time'] ?? null,
                selectedSeats: $validated['selected_seats'] ?? null,
                ticketCount: $validated['ticket_count'] ?? null,
                userName: $validated['user_name'] ?? null,
                phoneNumber: $validated['phone_number'] ?? null,
                email: $validated['email'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment failure logged successfully',
                'log_id' => $log->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log payment failure: ' . $e->getMessage(),
            ], 500);
        }
    }
}
