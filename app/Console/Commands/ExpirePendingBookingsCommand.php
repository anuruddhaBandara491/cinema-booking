<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\BookingFlowLogService;
use App\Services\SeatLockService;
use Illuminate\Console\Command;

class ExpirePendingBookingsCommand extends Command
{
    protected $signature = 'bookings:expire-pending';

    protected $description = 'Cancel PayHere bookings that are still unpaid after the seat hold ends';

    public function __construct(
        protected SeatLockService $seatLockService,
        protected BookingFlowLogService $flowLogService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $holdMinutes = (int) config('payhere.pending_hold_minutes', 30);
        $expiredBookings = Booking::query()
            ->where('payment_status', 'pending')
            ->where('created_at', '<', now()->subMinutes($holdMinutes))
            ->get();

        foreach ($expiredBookings as $booking) {
            $booking->payment_status = 'cancelled';
            $booking->save();

            if ($booking->session_id) {
                $this->seatLockService->releaseAllSessionLocks($booking->session_id);
            }

            $this->flowLogService->logPaymentFailure(
                sessionId: $booking->session_id ?: 'payhere_'.$booking->booking_reference,
                errorMessage: 'PayHere payment was not completed within the hold window.',
                movieId: $booking->movie_id,
                showDate: optional($booking->booking_date)->toDateString(),
                showTime: $booking->booking_time,
                selectedSeats: $booking->selected_seats,
                ticketCount: count($booking->selected_seats ?? []),
                userName: $booking->customer_name,
                phoneNumber: $booking->customer_phone,
                email: $booking->customer_email
            );
        }

        $count = $expiredBookings->count();
        $this->info($count === 0
            ? 'No pending bookings have passed the hold window.'
            : "Cancelled {$count} pending booking(s).");

        return self::SUCCESS;
    }
}
