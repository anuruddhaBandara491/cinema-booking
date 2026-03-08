<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Movie;
use App\Services\SeatLockService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    protected SeatLockService $seatLockService;

    public function __construct(SeatLockService $seatLockService)
    {
        $this->seatLockService = $seatLockService;
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
}
