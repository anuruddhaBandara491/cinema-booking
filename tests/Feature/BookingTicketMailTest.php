<?php

namespace Tests\Feature;

use App\Mail\BookingTicketMail;
use App\Models\Booking;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingTicketMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payhere.merchant_id' => '1210001',
            'payhere.merchant_secret' => 'test-secret',
            'payhere.currency' => 'LKR',
            'payhere.sandbox' => true,
        ]);
    }

    public function testSuccessfulPayherePaymentEmailsTheTicket(): void
    {
        Mail::fake();

        $booking = $this->pendingBooking();

        $this->post(route('payhere.notify'), $this->paidPayload($booking))
            ->assertOk();

        $booking->refresh();
        $this->assertSame('completed', $booking->payment_status);

        Mail::assertSent(BookingTicketMail::class, function (BookingTicketMail $mail) use ($booking) {
            $html = $mail->render();

            return $mail->hasTo('guest@example.com')
                && str_contains($html, $booking->booking_reference)
                && str_contains($html, 'Night Show')
                && str_contains($html, 'Sunday, 22 Nov 2026')
                && str_contains($html, '18:30')
                && str_contains($html, 'A12, A13')
                && str_contains($html, 'Ticket price: LKR 1,450.00')
                && ! str_contains($html, 'Amount paid')
                && ! str_contains($html, '1,500.00');
        });
    }

    public function testASecondPayhereSuccessDoesNotEmailAgain(): void
    {
        Mail::fake();

        $booking = $this->pendingBooking();
        $payload = $this->paidPayload($booking);

        $this->post(route('payhere.notify'), $payload)->assertOk();
        $this->post(route('payhere.notify'), $payload)->assertOk();

        Mail::assertSent(BookingTicketMail::class, 1);
    }

    public function testPaidConfirmationPageOffersAPrintableTicket(): void
    {
        $booking = $this->pendingBooking();
        $booking->payment_status = 'completed';
        $booking->booked_at = now();
        $booking->save();

        $this->get(route('bookings.confirmation', $booking))
            ->assertOk()
            ->assertSee('Print ticket', false)
            ->assertSee('id="bookingConfirmation__print_ticket"', false)
            ->assertSee('A copy of this ticket was sent to guest@example.com', false);
    }

    private function pendingBooking(): Booking
    {
        $movie = Movie::query()->create([
            'title' => 'Night Show',
            'show_start_date' => '2026-11-22',
            'show_end_date' => '2026-11-30',
            'show_times' => ['18:30'],
            'booking_charge' => 50,
            'is_published' => true,
        ]);

        return Booking::query()->create([
            'movie_id' => $movie->id,
            'booking_reference' => 'BK202611220001',
            'customer_name' => 'Nimal Perera',
            'customer_phone' => '0771234567',
            'customer_email' => 'guest@example.com',
            'booking_date' => '2026-11-22',
            'booking_time' => '18:30',
            'selected_seats' => ['A12', 'A13'],
            'tickets' => [],
            'total_amount' => '1500.00',
            'payment_method' => 'card',
            'payment_status' => 'pending',
            'session_id' => 'session-ticket-test',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function paidPayload(Booking $booking): array
    {
        $payload = [
            'merchant_id' => '1210001',
            'order_id' => $booking->booking_reference,
            'payhere_amount' => '1500.00',
            'payhere_currency' => 'LKR',
            'status_code' => '2',
            'payment_id' => '320012345',
            'method' => 'VISA',
        ];

        $payload['md5sig'] = strtoupper(md5(
            $payload['merchant_id']
            .$payload['order_id']
            .$payload['payhere_amount']
            .$payload['payhere_currency']
            .$payload['status_code']
            .strtoupper(md5('test-secret'))
        ));

        return $payload;
    }
}
