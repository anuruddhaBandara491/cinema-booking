<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Movie;
use App\Services\SeatLockService;
use App\Services\BookingFlowLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected SeatLockService $seatLockService;
    protected BookingFlowLogService $flowLogService;

    public function __construct(SeatLockService $seatLockService, BookingFlowLogService $flowLogService)
    {
        $this->seatLockService = $seatLockService;
        $this->flowLogService = $flowLogService;
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Validate the booking data
            $validated = $request->validate([
                'movie_id' => 'required|exists:movies,id',
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'customer_nic' => 'nullable|string|max:50',
                'booking_date' => 'required|date',
                'booking_time' => 'required|string',
                'selected_seats' => 'required|array|min:1',
                'selected_seats.*' => 'string',
                'tickets' => 'required|array',
                'session_id' => 'required|string',
                'total_amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:card,wallet,cash',
            ]);

            // Log payment success
            $this->flowLogService->logPaymentSuccess(
                sessionId: $validated['session_id'],
                movieId: (int)$validated['movie_id'],
                showDate: $validated['booking_date'],
                showTime: $validated['booking_time'],
                selectedSeats: $validated['selected_seats'],
                ticketCount: count($validated['selected_seats']),
                userName: $validated['customer_name'],
                phoneNumber: $validated['customer_phone'],
                email: $validated['customer_email']
            );

            // Create the booking with 'completed' payment status
            // (In production, you'd verify payment before setting this)
            $booking = new Booking();
            $booking->movie_id = $validated['movie_id'];
            $booking->booking_reference = Booking::generateReference();
            $booking->customer_name = $validated['customer_name'];
            $booking->customer_phone = $validated['customer_phone'];
            $booking->customer_email = $validated['customer_email'];
            $booking->customer_nic = $validated['customer_nic'];
            $booking->booking_date = $validated['booking_date'];
            $booking->booking_time = $validated['booking_time'];
            $booking->selected_seats = $validated['selected_seats'];
            $booking->tickets = $validated['tickets'];
            $booking->total_amount = $validated['total_amount'];
            $booking->payment_method = $validated['payment_method'];
            $booking->payment_status = 'completed'; // Mark as completed
            $booking->booked_at = now();
            $booking->save();

            // Release all locks for this session now that booking is confirmed
            $this->seatLockService->releaseAllSessionLocks($validated['session_id']);

            return redirect()->route('bookings.confirmation', $booking)->with('success', 'Booking submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Booking creation failed: ' . $e->getMessage());

            // Log payment failure
            if (!empty($validated['session_id'])) {
                $this->flowLogService->logPaymentFailure(
                    sessionId: $validated['session_id'],
                    errorMessage: $e->getMessage(),
                    movieId: isset($validated['movie_id']) ? (int)$validated['movie_id'] : null,
                    showDate: $validated['booking_date'] ?? null,
                    showTime: $validated['booking_time'] ?? null,
                    selectedSeats: $validated['selected_seats'] ?? null,
                    ticketCount: isset($validated['selected_seats']) ? count($validated['selected_seats']) : null,
                    userName: $validated['customer_name'] ?? null,
                    phoneNumber: $validated['customer_phone'] ?? null,
                    email: $validated['customer_email'] ?? null
                );
            }

            return back()->with('error', 'Failed to create booking: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show booking confirmation.
     */
    public function confirmation(Booking $booking): View
    {
        return view('bookings.confirmation', [
            'booking' => $booking,
        ]);
    }

    /**
     * Show booking details.
     */
    public function show(Booking $booking): View
    {
        return view('bookings.show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Display the counter booking interface.
     * Only accessible to users with manager or admin role.
     */
    public function counterBookingIndex(): View
    {
        $movies = Movie::where('book_now', true)
            ->orderBy('title')
            ->get();

        return view('bookings.counter-booking', [
            'movies' => $movies,
        ]);
    }

    /**
     * Store a counter booking via API request.
     * Creates a booking for a customer who paid in cash at the counter.
     */
    public function counterBookingStore(Request $request)
    {
        try {
            // Validate the counter booking data
            $validated = $request->validate([
                'movie_id' => 'required|exists:movies,id',
                'booking_date' => 'required|date',
                'booking_time' => 'required|string',
                'selected_seats' => 'required|array|min:1',
                'selected_seats.*' => 'string',
                'customer_name' => 'nullable|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'total_amount' => 'required|numeric|min:0',
            ]);

            // Verify seats are still available
            $bookedSeats = Booking::where('movie_id', $validated['movie_id'])
                ->where('booking_date', $validated['booking_date'])
                ->where('booking_time', $validated['booking_time'])
                ->where('payment_status', 'completed')
                ->get()
                ->flatMap(fn($booking) => $booking->selected_seats ?? [])
                ->unique()
                ->values()
                ->toArray();

            $conflictingSeats = array_intersect($validated['selected_seats'], $bookedSeats);
            if (!empty($conflictingSeats)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some seats are already booked: ' . implode(', ', $conflictingSeats),
                    'conflicting_seats' => $conflictingSeats,
                ], 409);
            }

            // Create the booking
            $booking = new Booking();
            $booking->movie_id = $validated['movie_id'];
            $booking->booking_reference = Booking::generateReference();
            $booking->customer_name = $validated['customer_name'] ?? 'Counter Walk-in';
            $booking->customer_phone = $validated['customer_phone'] ?? null;
            $booking->booking_date = $validated['booking_date'];
            $booking->booking_time = $validated['booking_time'];
            $booking->selected_seats = $validated['selected_seats'];
            $booking->tickets = []; // Counter bookings don't track ticket types separately
            $booking->total_amount = $validated['total_amount'];
            $booking->payment_method = 'cash';
            $booking->payment_status = 'paid';
            $booking->booked_at = now();
            $booking->save();

            Log::info('Counter booking created', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'seats' => $booking->selected_seats,
                'created_by' => Auth::user()->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully!',
                'booking' => [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'customer_name' => $booking->customer_name,
                    'customer_phone' => $booking->customer_phone,
                    'seats' => $booking->selected_seats,
                    'total_amount' => $booking->total_amount,
                    'booked_at' => $booking->booked_at->format('Y-m-d H:i:s'),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Counter booking creation failed', [
                'error' => $e->getMessage(),
                'user' => Auth::user()->name,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking: ' . $e->getMessage(),
            ], 500);
        }
    }
}
