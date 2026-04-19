<?php

namespace App\Services;

use App\Models\BookingFlowLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

/**
 * Service for logging booking flow actions.
 * Automatically captures session ID, IP address, and user agent.
 */
class BookingFlowLogService
{
    /**
     * Log a booking flow action.
     *
     * @param array $data The log data including:
     *   - session_id (optional, auto-generated if not provided)
     *   - step_name (required)
     *   - action (required)
     *   - user_name (optional)
     *   - phone_number (optional)
     *   - email (optional)
     *   - movie_id (optional)
     *   - show_date (optional)
     *   - show_time (optional)
     *   - ticket_count (optional)
     *   - selected_seats (optional, array)
     *   - status (optional, default: 'success')
     *   - error_message (optional)
     *
     * @return BookingFlowLog
     */
    public function log(array $data): BookingFlowLog
    {
        // Generate session ID if not provided
        if (empty($data['session_id'])) {
            $data['session_id'] = $this->generateSessionId();
        }

        // Capture system information
        $data['ip_address'] = Request::ip();
        $data['user_agent'] = Request::userAgent();

        // Set default status if not provided
        if (empty($data['status'])) {
            $data['status'] = 'success';
        }

        // Create and save the log
        return BookingFlowLog::create($data);
    }

    /**
     * Log user details step.
     *
     * @param string $sessionId
     * @param string $userName
     * @param string $phoneNumber
     * @param string|null $email
     * @param string $status
     * @param string|null $errorMessage
     *
     * @return BookingFlowLog
     */
    public function logUserDetails(
        string $sessionId,
        string $userName,
        string $phoneNumber,
        ?string $email = null,
        string $status = 'success',
        ?string $errorMessage = null
    ): BookingFlowLog {
        return $this->log([
            'session_id' => $sessionId,
            'step_name' => 'user_details',
            'action' => 'entered_details',
            'user_name' => $userName,
            'phone_number' => $phoneNumber,
            'email' => $email,
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Log movie selection step.
     *
     * @param string $sessionId
     * @param int $movieId
     * @param string $showDate (format: YYYY-MM-DD)
     * @param string $showTime (format: HH:MM)
     * @param string|null $userName
     * @param string|null $phoneNumber
     * @param string|null $email
     * @param string $status
     * @param string|null $errorMessage
     *
     * @return BookingFlowLog
     */
    public function logMovieSelection(
        string $sessionId,
        int $movieId,
        string $showDate,
        string $showTime,
        ?string $userName = null,
        ?string $phoneNumber = null,
        ?string $email = null,
        string $status = 'success',
        ?string $errorMessage = null
    ): BookingFlowLog {
        return $this->log([
            'session_id' => $sessionId,
            'step_name' => 'movie_selection',
            'action' => 'selected_movie',
            'user_name' => $userName,
            'phone_number' => $phoneNumber,
            'email' => $email,
            'movie_id' => $movieId,
            'show_date' => $showDate,
            'show_time' => $showTime,
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Log ticket count selection step.
     *
     * @param string $sessionId
     * @param int $ticketCount
     * @param int|null $movieId
     * @param string|null $showDate
     * @param string|null $showTime
     * @param string|null $userName
     * @param string|null $phoneNumber
     * @param string|null $email
     * @param string $status
     * @param string|null $errorMessage
     *
     * @return BookingFlowLog
     */
    public function logTicketCount(
        string $sessionId,
        int $ticketCount,
        ?int $movieId = null,
        ?string $showDate = null,
        ?string $showTime = null,
        ?string $userName = null,
        ?string $phoneNumber = null,
        ?string $email = null,
        string $status = 'success',
        ?string $errorMessage = null
    ): BookingFlowLog {
        return $this->log([
            'session_id' => $sessionId,
            'step_name' => 'ticket_count',
            'action' => 'selected_ticket_count',
            'user_name' => $userName,
            'phone_number' => $phoneNumber,
            'email' => $email,
            'movie_id' => $movieId,
            'show_date' => $showDate,
            'show_time' => $showTime,
            'ticket_count' => $ticketCount,
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Log seat selection step.
     *
     * @param string $sessionId
     * @param array $selectedSeats
     * @param int|null $movieId
     * @param string|null $showDate
     * @param string|null $showTime
     * @param int|null $ticketCount
     * @param string|null $userName
     * @param string|null $phoneNumber
     * @param string|null $email
     * @param string $status
     * @param string|null $errorMessage
     *
     * @return BookingFlowLog
     */
    public function logSeatSelection(
        string $sessionId,
        array $selectedSeats,
        ?int $movieId = null,
        ?string $showDate = null,
        ?string $showTime = null,
        ?int $ticketCount = null,
        ?string $userName = null,
        ?string $phoneNumber = null,
        ?string $email = null,
        string $status = 'success',
        ?string $errorMessage = null
    ): BookingFlowLog {
        return $this->log([
            'session_id' => $sessionId,
            'step_name' => 'seat_selection',
            'action' => 'selected_seats',
            'user_name' => $userName,
            'phone_number' => $phoneNumber,
            'email' => $email,
            'movie_id' => $movieId,
            'show_date' => $showDate,
            'show_time' => $showTime,
            'ticket_count' => $ticketCount,
            'selected_seats' => $selectedSeats,
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Log payment attempt.
     *
     * @param string $sessionId
     * @param int|null $movieId
     * @param string|null $showDate
     * @param string|null $showTime
     * @param array|null $selectedSeats
     * @param int|null $ticketCount
     * @param string|null $userName
     * @param string|null $phoneNumber
     * @param string|null $email
     *
     * @return BookingFlowLog
     */
    public function logPaymentAttempt(
        string $sessionId,
        ?int $movieId = null,
        ?string $showDate = null,
        ?string $showTime = null,
        ?array $selectedSeats = null,
        ?int $ticketCount = null,
        ?string $userName = null,
        ?string $phoneNumber = null,
        ?string $email = null
    ): BookingFlowLog {
        return $this->log([
            'session_id' => $sessionId,
            'step_name' => 'payment',
            'action' => 'payment_attempt',
            'user_name' => $userName,
            'phone_number' => $phoneNumber,
            'email' => $email,
            'movie_id' => $movieId,
            'show_date' => $showDate,
            'show_time' => $showTime,
            'ticket_count' => $ticketCount,
            'selected_seats' => $selectedSeats,
            'status' => 'success',
        ]);
    }

    /**
     * Log successful payment.
     *
     * @param string $sessionId
     * @param int|null $movieId
     * @param string|null $showDate
     * @param string|null $showTime
     * @param array|null $selectedSeats
     * @param int|null $ticketCount
     * @param string|null $userName
     * @param string|null $phoneNumber
     * @param string|null $email
     *
     * @return BookingFlowLog
     */
    public function logPaymentSuccess(
        string $sessionId,
        ?int $movieId = null,
        ?string $showDate = null,
        ?string $showTime = null,
        ?array $selectedSeats = null,
        ?int $ticketCount = null,
        ?string $userName = null,
        ?string $phoneNumber = null,
        ?string $email = null
    ): BookingFlowLog {
        return $this->log([
            'session_id' => $sessionId,
            'step_name' => 'payment',
            'action' => 'payment_success',
            'user_name' => $userName,
            'phone_number' => $phoneNumber,
            'email' => $email,
            'movie_id' => $movieId,
            'show_date' => $showDate,
            'show_time' => $showTime,
            'ticket_count' => $ticketCount,
            'selected_seats' => $selectedSeats,
            'status' => 'success',
        ]);
    }

    /**
     * Log payment failure.
     *
     * @param string $sessionId
     * @param string|null $errorMessage
     * @param int|null $movieId
     * @param string|null $showDate
     * @param string|null $showTime
     * @param array|null $selectedSeats
     * @param int|null $ticketCount
     * @param string|null $userName
     * @param string|null $phoneNumber
     * @param string|null $email
     *
     * @return BookingFlowLog
     */
    public function logPaymentFailure(
        string $sessionId,
        ?string $errorMessage = null,
        ?int $movieId = null,
        ?string $showDate = null,
        ?string $showTime = null,
        ?array $selectedSeats = null,
        ?int $ticketCount = null,
        ?string $userName = null,
        ?string $phoneNumber = null,
        ?string $email = null
    ): BookingFlowLog {
        return $this->log([
            'session_id' => $sessionId,
            'step_name' => 'payment',
            'action' => 'payment_failed',
            'user_name' => $userName,
            'phone_number' => $phoneNumber,
            'email' => $email,
            'movie_id' => $movieId,
            'show_date' => $showDate,
            'show_time' => $showTime,
            'ticket_count' => $ticketCount,
            'selected_seats' => $selectedSeats,
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Generate a unique session ID for a booking flow.
     *
     * @return string
     */
    public function generateSessionId(): string
    {
        return 'session_' . Str::random(32) . '_' . now()->timestamp;
    }

    /**
     * Get session timeline for a specific session.
     *
     * @param string $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSessionTimeline(string $sessionId)
    {
        return BookingFlowLog::bySession($sessionId)->get();
    }

    /**
     * Get last log entry for a session.
     *
     * @param string $sessionId
     * @return BookingFlowLog|null
     */
    public function getLastLog(string $sessionId): ?BookingFlowLog
    {
        return BookingFlowLog::where('session_id', $sessionId)
            ->latest()
            ->first();
    }

    /**
     * Check if a booking session is complete.
     *
     * @param string $sessionId
     * @return bool
     */
    public function isSessionComplete(string $sessionId): bool
    {
        return BookingFlowLog::where('session_id', $sessionId)
            ->where('step_name', 'payment')
            ->where('action', 'payment_success')
            ->where('status', 'success')
            ->exists();
    }

    /**
     * Check if a booking session has failed payment.
     *
     * @param string $sessionId
     * @return bool
     */
    public function hasPaymentFailed(string $sessionId): bool
    {
        return BookingFlowLog::where('session_id', $sessionId)
            ->where('step_name', 'payment')
            ->where('action', 'payment_failed')
            ->exists();
    }
}
