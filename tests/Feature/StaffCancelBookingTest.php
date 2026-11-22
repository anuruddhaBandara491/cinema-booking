<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Movie;
use App\Models\SeatLock;
use App\Models\User;
use App\Services\SeatLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffCancelBookingTest extends TestCase
{
    use RefreshDatabase;

    public function testManagerCanCancelACompletedBookingAndFreeTheSeats(): void
    {
        $manager = $this->manager();
        $movie = $this->movie();
        $booking = $this->booking($movie, 'BK-DONE', 'completed');

        SeatLock::query()->create([
            'movie_id' => $movie->id,
            'show_date' => '2026-11-22',
            'show_time' => '18:30',
            'seat_number' => 'A12',
            'session_id' => $booking->session_id,
            'locked_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        $heldBefore = app(SeatLockService::class)->heldSeatNumbers($movie->id, '2026-11-22', '18:30');
        $this->assertTrue($heldBefore->contains('A12'));

        $this->actingAs($manager)
            ->post(route('bookings.cancel', $booking), [
                'cancellation_reason' => 'Sold the wrong seats',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $booking->refresh();
        $this->assertSame('cancelled', $booking->payment_status);
        $this->assertSame($manager->id, $booking->cancelled_by);
        $this->assertSame('Sold the wrong seats', $booking->cancellation_reason);
        $this->assertNotNull($booking->cancelled_at);
        $this->assertFalse(
            app(SeatLockService::class)->heldSeatNumbers($movie->id, '2026-11-22', '18:30')->contains('A12')
        );
        $this->assertDatabaseMissing('seat_locks', [
            'session_id' => $booking->session_id,
        ]);
    }

    public function testGuestCannotOpenOrCancelBookings(): void
    {
        $booking = $this->booking($this->movie(), 'BK-GUEST', 'completed');

        $this->get(route('bookings.manage'))->assertRedirect(route('login'));
        $this->post(route('bookings.cancel', $booking), [
            'cancellation_reason' => 'Should not work',
        ])->assertRedirect(route('login'));

        $this->assertSame('completed', $booking->fresh()->payment_status);
    }

    public function testACancelledBookingCannotBeCancelledAgain(): void
    {
        $manager = $this->manager();
        $booking = $this->booking($this->movie(), 'BK-TWICE', 'completed');

        $this->actingAs($manager)->post(route('bookings.cancel', $booking), [
            'cancellation_reason' => 'First reason',
        ])->assertSessionHas('status');

        $this->actingAs($manager)->post(route('bookings.cancel', $booking), [
            'cancellation_reason' => 'Second reason',
        ])->assertSessionHas('error');

        $booking->refresh();
        $this->assertSame('cancelled', $booking->payment_status);
        $this->assertSame('First reason', $booking->cancellation_reason);
    }

    public function testManagerCanCancelAPendingBookingBeforeTheHoldEnds(): void
    {
        $manager = $this->manager();
        $movie = $this->movie();
        $booking = $this->booking($movie, 'BK-HOLD', 'pending');

        $this->assertTrue(
            app(SeatLockService::class)->heldSeatNumbers($movie->id, '2026-11-22', '18:30')->contains('A12')
        );

        $this->actingAs($manager)->post(route('bookings.cancel', $booking), [
            'cancellation_reason' => 'Customer left the counter',
        ])->assertSessionHas('status');

        $this->assertSame('cancelled', $booking->fresh()->payment_status);
        $this->assertFalse(
            app(SeatLockService::class)->heldSeatNumbers($movie->id, '2026-11-22', '18:30')->contains('A12')
        );
    }

    private function manager(): User
    {
        $manager = User::factory()->create(['name' => 'Counter Manager']);
        Role::findOrCreate('manager');
        $manager->assignRole('manager');

        return $manager;
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
}
