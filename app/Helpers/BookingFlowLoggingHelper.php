<?php

/**
 * Booking Flow Logging System - Integration Helper
 *
 * This file provides helper functions and examples for integrating
 * the booking flow logging system into your application.
 */

// Access the service via dependency injection in controllers
// use App\Services\BookingFlowLogService;
// protected BookingFlowLogService $flowLogService;
// public function __construct(BookingFlowLogService $flowLogService)
// {
//     $this->flowLogService = $flowLogService;
// }

// ============================================================
// FRONTEND INTEGRATION - API CALLS
// ============================================================

// Step 1: Log User Details (called when user submits name, phone, email)
/*
fetch('/api/log/user-details', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        session_id: sessionId,  // Generated once per booking flow
        user_name: 'John Doe',
        phone_number: '0712345678',
        email: 'john@example.com'
    })
})
.then(response => response.json())
.then(data => console.log('User details logged', data));
*/

// Step 2: Log Movie Selection (called when user selects movie, date, time)
/*
fetch('/api/log/movie-selection', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        session_id: sessionId,
        movie_id: 5,
        show_date: '2026-03-15',
        show_time: '18:30',
        user_name: 'John Doe',
        phone_number: '0712345678',
        email: 'john@example.com'
    })
})
.then(response => response.json())
.then(data => console.log('Movie selection logged', data));
*/

// Step 3: Log Ticket Count (called when user selects ticket quantity)
/*
fetch('/api/log/ticket-count', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        session_id: sessionId,
        ticket_count: 4,
        movie_id: 5,
        show_date: '2026-03-15',
        show_time: '18:30',
        user_name: 'John Doe',
        phone_number: '0712345678',
        email: 'john@example.com'
    })
})
.then(response => response.json())
.then(data => console.log('Ticket count logged', data));
*/

// Step 4: Log Seat Selection (called when user selects seats)
/*
fetch('/api/log/seat-selection', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        session_id: sessionId,
        selected_seats: ['A1', 'A2', 'A3', 'A4'],
        movie_id: 5,
        show_date: '2026-03-15',
        show_time: '18:30',
        ticket_count: 4,
        user_name: 'John Doe',
        phone_number: '0712345678',
        email: 'john@example.com'
    })
})
.then(response => response.json())
.then(data => console.log('Seat selection logged', data));
*/

// Step 5a: Log Payment Attempt (called before redirecting to payment gateway)
/*
fetch('/api/log/payment-attempt', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        session_id: sessionId,
        movie_id: 5,
        show_date: '2026-03-15',
        show_time: '18:30',
        selected_seats: ['A1', 'A2', 'A3', 'A4'],
        ticket_count: 4,
        user_name: 'John Doe',
        phone_number: '0712345678',
        email: 'john@example.com'
    })
})
.then(response => response.json())
.then(data => console.log('Payment attempt logged', data));
*/

// Step 5b: Log Payment Failure (called if payment fails)
/*
fetch('/api/log/payment-failure', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        session_id: sessionId,
        error_message: 'Card declined',
        movie_id: 5,
        show_date: '2026-03-15',
        show_time: '18:30',
        selected_seats: ['A1', 'A2', 'A3', 'A4'],
        ticket_count: 4,
        user_name: 'John Doe',
        phone_number: '0712345678',
        email: 'john@example.com'
    })
})
.then(response => response.json())
.then(data => console.log('Payment failure logged', data));
*/

// ============================================================
// BACKEND INTEGRATION - SERVICE USAGE
// ============================================================

// Example 1: Log user details in a controller
/*
public function submitUserDetails(Request $request, BookingFlowLogService $flowLogService)
{
    $validated = $request->validate([
        'session_id' => 'required|string',
        'user_name' => 'required|string',
        'phone_number' => 'required|string',
        'email' => 'nullable|email',
    ]);

    // Log the action
    $flowLogService->logUserDetails(
        sessionId: $validated['session_id'],
        userName: $validated['user_name'],
        phoneNumber: $validated['phone_number'],
        email: $validated['email'] ?? null
    );

    return response()->json(['success' => true]);
}
*/

// Example 2: Log movie selection
/*
public function selectMovie(Request $request, BookingFlowLogService $flowLogService)
{
    $validated = $request->validate([
        'session_id' => 'required|string',
        'movie_id' => 'required|integer',
        'show_date' => 'required|date',
        'show_time' => 'required|string',
    ]);

    $flowLogService->logMovieSelection(
        sessionId: $validated['session_id'],
        movieId: $validated['movie_id'],
        showDate: $validated['show_date'],
        showTime: $validated['show_time']
    );

    return response()->json(['success' => true]);
}
*/

// Example 3: Using the generic log method
/*
$flowLogService->log([
    'session_id' => $sessionId,
    'step_name' => 'custom_step',
    'action' => 'custom_action',
    'user_name' => 'John Doe',
    'phone_number' => '0712345678',
    'status' => 'success'
]);
*/

// ============================================================
// HELPER FUNCTIONS
// ============================================================

// Generate a session ID for frontend to store
/*
const sessionId = 'session_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
// Store in sessionStorage or localStorage
sessionStorage.setItem('bookingSessionId', sessionId);
*/

// Retrieve session ID
/*
const sessionId = sessionStorage.getItem('bookingSessionId');
*/

// Helper function to make logging calls
/*
function logBookingEvent(event, data = {}) {
    const sessionId = sessionStorage.getItem('bookingSessionId');

    fetch(`/api/log/${event}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            session_id: sessionId,
            ...data
        })
    })
    .then(response => response.json())
    .catch(error => console.error('Logging error:', error));
}

// Usage:
logBookingEvent('user-details', {
    user_name: 'John',
    phone_number: '0712345678',
    email: 'john@example.com'
});
*/

// ============================================================
// ADMIN FEATURES ACCESS
// ============================================================

// Admin users can access:
// 1. /admin/booking-flow-logs - View all logs with filters
// 2. /admin/booking-flow-logs/session/{sessionId} - View timeline for specific session
// 3. /admin/booking-flow-logs/abandoned - View abandoned bookings report
// 4. /admin/booking-flow-logs/failed-payments - View failed payment report
// 5. /admin/booking-flow-logs/performance - View performance metrics

// ============================================================
// DATABASE QUERIES
// ============================================================

// Get all logs for a session
/*
use App\Models\BookingFlowLog;

$logs = BookingFlowLog::bySession($sessionId)->get();
*/

// Get abandoned bookings
/*
$abandoned = BookingFlowLog::getAbandonedBookingsSessions();
*/

// Get failed payments
/*
$failedPayments = BookingFlowLog::getFailedPayments();
*/

// Get performance metrics
/*
$metrics = BookingFlowLog::getStepPerformanceMetrics();
*/

// Get step duration for a session
/*
$duration = BookingFlowLog::getStepDuration($sessionId, 'seat_selection');
*/

// ============================================================
// MIGRATION AND SETUP
// ============================================================

// Run migration:
// php artisan migrate

// The migration creates the booking_flow_logs table with:
// - session_id (indexed for quick lookups)
// - step_name (user_details, movie_selection, ticket_count, seat_selection, payment)
// - action (entered_details, selected_movie, selected_ticket_count, selected_seats, payment_attempt, payment_success, payment_failed)
// - user details (name, phone, email)
// - booking details (movie_id, show_date, show_time, ticket_count, selected_seats)
// - system info (ip_address, user_agent)
// - status (success, failed)
// - error_message (nullable for failures)
// - timestamps

// ============================================================
// BEST PRACTICES
// ============================================================

// 1. Generate a unique session ID at the start of booking flow
// 2. Store session ID in sessionStorage or HTML hidden field
// 3. Log every step transition
// 4. Include all available user and booking data in each log
// 5. Log payment attempts and failures immediately
// 6. Use the BookingFlowLogService for consistency
// 7. Only admins can access log viewing features
// 8. Review abandoned and failed bookings regularly
// 9. Use performance metrics to identify bottlenecks
// 10. Set up cron jobs to clean old logs if needed

// ============================================================
// TROUBLESHOOTING
// ============================================================

// If logs aren't appearing:
// 1. Verify session ID is being passed correctly
// 2. Check database connection
// 3. Verify user is authenticated for admin pages
// 4. Check Laravel logs in storage/logs/
// 5. Verify API routes are registered in routes/api.php
// 6. Check CSRF token is included in POST requests

// ============================================================
