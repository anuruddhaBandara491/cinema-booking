<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Movie;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTicketPriceTest extends TestCase
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

    public function testMixedTicketsAreChargedPerPersonPlusOneHandlingFee(): void
    {
        $movie = $this->movie();
        $odc = TicketType::query()->create([
            'name' => 'ODC',
            'adult_price' => 600,
            'has_child' => true,
            'child_price' => 400,
        ]);
        $box = TicketType::query()->create([
            'name' => 'BOX',
            'adult_price' => 800,
            'has_child' => false,
        ]);

        $response = $this->from('/movies')->post(route('bookings.store'), [
            'movie_id' => $movie->id,
            'customer_name' => 'Nimal Perera',
            'customer_phone' => '0771234567',
            'customer_email' => 'guest@example.com',
            'booking_date' => '2026-11-24',
            'booking_time' => '10:00',
            'selected_seats' => ['A1', 'A2', 'G1'],
            'tickets' => [
                $odc->id => ['adult' => 1, 'child' => 1],
                $box->id => ['adult' => 2, 'child' => 0],
            ],
            'session_id' => 'sess_price_test',
            'total_amount' => 1,
            'payment_method' => 'card',
        ]);

        $booking = Booking::query()->firstOrFail();

        $response->assertOk();
        $response->assertSee('https://sandbox.payhere.lk/pay/checkout', false);
        $response->assertSee('id="bookingPayment__payhere_form"', false);
        $response->assertDontSee('Continue to PayHere', false);
        $this->assertSame('2635.00', number_format((float) $booking->total_amount, 2, '.', ''));
    }

    public function testBookingIsRejectedWhenSeatsDoNotMatchTickets(): void
    {
        $movie = $this->movie();
        $odc = TicketType::query()->create([
            'name' => 'ODC',
            'adult_price' => 600,
            'has_child' => true,
            'child_price' => 400,
        ]);

        $response = $this->from(route('movies.index'))->post(route('bookings.store'), [
            'movie_id' => $movie->id,
            'customer_name' => 'Nimal Perera',
            'customer_phone' => '0771234567',
            'customer_email' => 'guest@example.com',
            'booking_date' => '2026-11-24',
            'booking_time' => '10:00',
            'selected_seats' => ['A1'],
            'tickets' => [
                $odc->id => ['adult' => 1, 'child' => 1],
            ],
            'session_id' => 'sess_mismatch_test',
            'total_amount' => 1035,
            'payment_method' => 'card',
        ]);

        $response->assertRedirect(route('movies.index'));
        $response->assertSessionHas('error', 'The selected seats do not match the tickets.');
        $this->assertSame(0, Booking::query()->count());
    }

    private function movie(): Movie
    {
        return Movie::query()->create([
            'title' => 'Dharmayuddhaya 2',
            'show_start_date' => '2026-11-01',
            'show_end_date' => '2026-12-31',
            'show_times' => ['10:00'],
            'booking_charge' => 35,
            'is_published' => true,
        ]);
    }
}
