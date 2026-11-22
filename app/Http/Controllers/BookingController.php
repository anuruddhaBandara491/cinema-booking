<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Movie;
use App\Models\TicketType;
use App\Services\SeatLockService;
use App\Services\BookingFlowLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
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
        $validated = [];

        try {
            $this->decodeJsonFields($request, ['selected_seats', 'tickets']);

            $validated = $request->validate([
                'movie_id' => 'required|exists:movies,id',
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'required|email|max:255',
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

            $movie = Movie::query()->findOrFail($validated['movie_id']);
            $amount = $this->calculatePayableAmount($movie, $validated['selected_seats']);

            if ((float) $amount <= 0) {
                return back()->with('error', 'This booking has no amount to pay.')->withInput();
            }

            $heldSeats = $this->seatLockService
                ->heldSeatNumbers($movie->id, $validated['booking_date'], $validated['booking_time'])
                ->all();
            $conflictingSeats = array_values(array_intersect($validated['selected_seats'], $heldSeats));

            if ($conflictingSeats !== []) {
                return back()
                    ->with('error', 'Some seats are no longer available: '.implode(', ', $conflictingSeats))
                    ->withInput();
            }

            $this->flowLogService->logPaymentAttempt(
                sessionId: $validated['session_id'],
                movieId: (int) $validated['movie_id'],
                showDate: $validated['booking_date'],
                showTime: $validated['booking_time'],
                selectedSeats: $validated['selected_seats'],
                ticketCount: count($validated['selected_seats']),
                userName: $validated['customer_name'],
                phoneNumber: $validated['customer_phone'],
                email: $validated['customer_email']
            );

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
            $booking->total_amount = $amount;
            $booking->payment_method = 'card';
            $booking->payment_status = 'pending';
            $booking->session_id = $validated['session_id'];
            $booking->save();

            return redirect()->route('bookings.payhere.checkout', $booking);
        } catch (ValidationException $exception) {
            throw $exception;
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
     * @param  array<int, string>  $fields
     */
    private function decodeJsonFields(Request $request, array $fields): void
    {
        $decoded = [];

        foreach ($fields as $field) {
            $value = $request->input($field);

            if (! is_string($value)) {
                continue;
            }

            $parsed = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $decoded[$field] = $parsed;
            }
        }

        if ($decoded !== []) {
            $request->merge($decoded);
        }
    }

    /**
     * Charge the same amount the booking screen shows: one adult price per
     * selected seat, plus the movie handling fee.
     *
     * @param  array<int, string>  $seats
     */
    private function calculatePayableAmount(Movie $movie, array $seats): string
    {
        $ticketTypes = TicketType::query()->get()->keyBy(fn (TicketType $type) => strtolower($type->name));
        $total = 0.0;

        foreach ($seats as $seat) {
            $row = strtoupper(substr((string) $seat, 0, 1));
            $typeName = in_array($row, ['G', 'H'], true) ? 'box' : 'odc';
            $total += (float) ($ticketTypes->get($typeName)?->adult_price ?? 0);
        }

        $total += (float) ($movie->booking_charge ?? 0);

        return number_format($total, 2, '.', '');
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
     * List bookings for a show date so staff can void one.
     */
    public function manage(Request $request): View
    {
        $showDate = (string) $request->input('show_date', now()->toDateString());
        $search = trim((string) $request->input('search', ''));

        $bookings = Booking::query()
            ->with(['movie', 'cancelledBy'])
            ->whereDate('booking_date', $showDate)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('booking_reference', 'like', '%'.$search.'%')
                        ->orWhere('customer_phone', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('booking_time')
            ->orderByDesc('id')
            ->get();

        return view('bookings.manage', [
            'bookings' => $bookings,
            'showDate' => $showDate,
            'search' => $search,
        ]);
    }

    /**
     * Void a pending or paid booking and release its seats.
     */
    public function cancelByStaff(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ]);

        if (! in_array($booking->payment_status, ['pending', 'completed', 'paid'], true)) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        $staff = $request->user();
        $booking->payment_status = 'cancelled';
        $booking->cancelled_at = now();
        $booking->cancelled_by = $staff->id;
        $booking->cancellation_reason = $validated['cancellation_reason'];
        $booking->save();

        if ($booking->session_id) {
            $this->seatLockService->releaseAllSessionLocks($booking->session_id);
        }

        $this->flowLogService->logPaymentFailure(
            sessionId: $booking->session_id ?: 'staff_'.$booking->booking_reference,
            errorMessage: 'Cancelled by '.$staff->name.': '.$validated['cancellation_reason'],
            movieId: $booking->movie_id,
            showDate: optional($booking->booking_date)->toDateString(),
            showTime: $booking->booking_time,
            selectedSeats: $booking->selected_seats,
            ticketCount: count($booking->selected_seats ?? []),
            userName: $booking->customer_name,
            phoneNumber: $booking->customer_phone,
            email: $booking->customer_email
        );

        return back()->with('status', 'Booking '.$booking->booking_reference.' was cancelled. Return any payment at the counter or from PayHere.');
    }

    /**
     * Display the counter booking interface.
     * Only accessible to users with manager or admin role.
     */
    public function counterBookingIndex(): View
    {
        $movies = Movie::where('book_now', true)
            ->whereDate('show_end_date', '>=', now()->toDateString())
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
            $bookedSeats = $this->seatLockService
                ->heldSeatNumbers($validated['movie_id'], $validated['booking_date'], $validated['booking_time'])
                ->all();

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
