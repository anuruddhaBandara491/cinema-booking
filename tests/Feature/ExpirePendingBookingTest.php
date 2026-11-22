<?php

namespace Tests\Feature;

use App\Mail\BookingTicketMail;
use App\Models\Booking;
use App\Models\Movie;
use App\Models\SeatLock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExpirePendingBookingTest extends TestCase
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
            'payhere.pending_hold_minutes' => 30,
        ]);
    }

    public function testPendingBookingsOlderThanTheHoldAreCancelled(): void
    {
        $movie = $this->movie();
        $expired = $this->booking($movie, 'BK-OLD', 'pending');
        $expired->created_at = now()->subMinutes(31);
        $expired->save();

        $fresh = $this->booking($movie, 'BK-NEW', 'pending', ['B1']);
        $completed = $this->booking($movie, 'BK-DONE', 'completed', ['C1']);
        $completed->created_at = now()->subHours(2);
        $completed->save();

        SeatLock::query()->create([
            'movie_id' => $movie->id,
            'show_date' => '2026-11-22',
            'show_time' => '18:30',
            'seat_number' => 'A12',
            'session_id' => $expired->session_id,
            'locked_at' => now()->subMinutes(31),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->artisan('bookings:expire-pending')->assertSuccessful();

        $this->assertSame('cancelled', $expired->fresh()->payment_status);
        $this->assertSame('pending', $fresh->fresh()->payment_status);
        $this->assertSame('completed', $completed->fresh()->payment_status);
        $this->assertDatabaseMissing('seat_locks', [
            'session_id' => $expired->session_id,
        ]);
    }

    public function testLatePayhereSuccessCompletesTheBookingWhenSeatsAreFree(): void
    {
        Mail::fake();

        $booking = $this->booking($this->movie(), 'BK-LATE', 'cancelled');
        $booking->created_at = now()->subMinutes(40);
        $booking->save();

        $this->post(route('payhere.notify'), $this->paidPayload($booking))->assertOk();

        $this->assertSame('completed', $booking->fresh()->payment_status);
        Mail::assertSent(BookingTicketMail::class);
    }

    public function testLatePayhereSuccessIsRejectedWhenSeatsAreAlreadySold(): void
    {
        Mail::fake();

        $movie = $this->movie();
        $this->booking($movie, 'BK-SOLD', 'completed', ['A12']);

        $late = $this->booking($movie, 'BK-LATE-SOLD', 'pending', ['A12', 'A13']);
        $late->created_at = now()->subMinutes(40);
        $late->save();

        $this->post(route('payhere.notify'), $this->paidPayload($late))->assertOk();

        $late->refresh();
        $this->assertSame('failed', $late->payment_status);
        $this->assertNull($late->booked_at);
        Mail::assertNothingSent();
    }

    private function movie(): Movie
    {
        return Movie::query()->create([
            'title' => 'Night Show',
            'show_start_date' => '2026-11-22',
            'show_end_date' => '2026-11-30',
            'show_times' => ['18:30'],
            'booking_charge' => 50,
            'is_published' => true,
        ]);
    }

    /**
     * @param  array<int, string>  $seats
     */
    private function booking(Movie $movie, string $reference, string $status, array $seats = ['A12', 'A13']): Booking
    {
        return Booking::query()->create([
            'movie_id' => $movie->id,
            'booking_reference' => $reference,
            'customer_name' => 'Nimal Perera',
            'customer_phone' => '0771234567',
            'customer_email' => 'guest@example.com',
            'booking_date' => '2026-11-22',
            'booking_time' => '18:30',
            'selected_seats' => $seats,
            'tickets' => [],
            'total_amount' => '1500.00',
            'payment_method' => 'card',
            'payment_status' => $status,
            'session_id' => 'session-'.$reference,
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
