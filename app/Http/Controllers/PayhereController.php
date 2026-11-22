<?php

namespace App\Http\Controllers;

use App\Mail\BookingTicketMail;
use App\Models\Booking;
use App\Services\BookingFlowLogService;
use App\Services\PayhereService;
use App\Services\SeatLockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PayhereController extends Controller
{
    public function __construct(
        protected PayhereService $payhereService,
        protected SeatLockService $seatLockService,
        protected BookingFlowLogService $flowLogService
    ) {
    }

    public function checkout(Booking $booking): View|RedirectResponse
    {
        $booking->loadMissing('movie');

        if ($booking->payment_status !== 'pending') {
            return redirect()->route('bookings.confirmation', $booking);
        }

        if (! $this->payhereService->isConfigured()) {
            return view('bookings.payhere-checkout', [
                'booking' => $booking,
                'checkoutUrl' => null,
                'fields' => [],
                'sandbox' => $this->payhereService->isSandbox(),
            ]);
        }

        return view('bookings.payhere-checkout', [
            'booking' => $booking,
            'checkoutUrl' => $this->payhereService->checkoutUrl(),
            'fields' => $this->payhereService->checkoutFields($booking),
            'sandbox' => $this->payhereService->isSandbox(),
        ]);
    }

    public function paymentReturn(Booking $booking): RedirectResponse
    {
        return redirect()->route('bookings.confirmation', $booking);
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->payment_status === 'pending') {
            $booking->payment_status = 'cancelled';
            $booking->save();
            $this->releaseBookingSeats($booking);
            $this->logPaymentFailure($booking, 'Customer cancelled the PayHere payment.');
        }

        return redirect()
            ->route('bookings.confirmation', $booking)
            ->with('error', 'Payment was cancelled. Your seats were released.');
    }

    public function notify(Request $request): Response
    {
        $payload = $request->all();
        $orderId = (string) ($payload['order_id'] ?? '');

        $booking = Booking::query()
            ->where('booking_reference', $orderId)
            ->first();

        if (! $booking) {
            Log::warning('PayHere notification for an unknown order.', ['order_id' => $orderId]);

            return response('Unknown order', 404);
        }

        if (! $this->payhereService->verifyNotification($payload)) {
            Log::warning('PayHere notification failed signature verification.', [
                'order_id' => $orderId,
                'booking_id' => $booking->id,
            ]);

            return response('Invalid signature', 400);
        }

        $statusCode = (int) ($payload['status_code'] ?? 0);

        if ($booking->payment_status === 'completed' && $statusCode !== -3) {
            return response('OK', 200);
        }
        $notifiedAmount = $this->payhereService->formatAmount((string) ($payload['payhere_amount'] ?? '0'));
        $expectedAmount = $this->payhereService->formatAmount($booking->total_amount);
        $currency = strtoupper((string) ($payload['payhere_currency'] ?? ''));

        if (! empty($payload['payment_id'])) {
            $booking->payment_reference = (string) $payload['payment_id'];
        }

        if ($statusCode === 2) {
            if ($notifiedAmount !== $expectedAmount || $currency !== (string) config('payhere.currency', 'LKR')) {
                $this->logPaymentFailure($booking, 'PayHere amount or currency did not match the booking.');
                $booking->save();

                return response('Amount mismatch', 400);
            }

            $soldSeats = $this->seatLockService->seatsSoldOnOtherBookings($booking);

            if ($soldSeats !== []) {
                $booking->payment_status = 'failed';
                $booking->save();
                $this->releaseBookingSeats($booking);
                $this->logPaymentFailure(
                    $booking,
                    'PayHere payment arrived after the seats were sold: '.implode(', ', $soldSeats)
                );

                Log::warning('PayHere payment succeeded after the seats were sold.', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'seats' => $soldSeats,
                ]);

                return response('OK', 200);
            }

            $booking->payment_status = 'completed';
            $booking->booked_at = $booking->booked_at ?? now();

            if (! empty($payload['method'])) {
                $booking->payment_method = strtolower((string) $payload['method']);
            }

            $booking->save();
            $this->releaseBookingSeats($booking);
            $this->logPaymentSuccess($booking);
            $this->sendTicketEmail($booking);

            return response('OK', 200);
        }

        if ($statusCode === 0) {
            $booking->save();

            return response('OK', 200);
        }

        $booking->payment_status = $statusCode === -1 ? 'cancelled' : 'failed';
        $booking->save();
        $this->releaseBookingSeats($booking);
        $this->logPaymentFailure(
            $booking,
            (string) ($payload['status_message'] ?? 'PayHere status code '.$statusCode)
        );

        return response('OK', 200);
    }

    private function releaseBookingSeats(Booking $booking): void
    {
        if ($booking->session_id) {
            $this->seatLockService->releaseAllSessionLocks($booking->session_id);
        }
    }

    private function logPaymentSuccess(Booking $booking): void
    {
        $this->flowLogService->logPaymentSuccess(
            sessionId: $this->logSessionId($booking),
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

    private function logPaymentFailure(Booking $booking, string $message): void
    {
        $this->flowLogService->logPaymentFailure(
            sessionId: $this->logSessionId($booking),
            errorMessage: $message,
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

    private function logSessionId(Booking $booking): string
    {
        return $booking->session_id ?: 'payhere_'.$booking->booking_reference;
    }

    private function sendTicketEmail(Booking $booking): void
    {
        if (! $booking->customer_email) {
            return;
        }

        try {
            $booking->loadMissing('movie');
            Mail::to($booking->customer_email)->send(new BookingTicketMail($booking));
        } catch (\Throwable $exception) {
            Log::error('Booking ticket email failed.', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
