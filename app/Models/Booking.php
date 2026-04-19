<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'movie_id',
        'booking_reference',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_nic',
        'booking_date',
        'booking_time',
        'selected_seats',
        'tickets',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_reference',
        'booked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'booked_at' => 'datetime',
        'selected_seats' => 'array',
        'tickets' => 'array',
    ];

    /**
     * Get the movie associated with the booking.
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Generate a unique booking reference.
     */
    public static function generateReference(): string
    {
        do {
            $reference = strtoupper('BK' . date('Ymd') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT));
        } while (self::where('booking_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Get total number of seats booked.
     */
    public function getTotalSeatsAttribute(): int
    {
        return count($this->selected_seats ?? []);
    }

    /**
     * Get total number of tickets.
     */
    public function getTotalTicketsAttribute(): int
    {
        $tickets = $this->tickets ?? [];
        $total = 0;
        foreach ($tickets as $ticketData) {
            $total += ($ticketData['adult'] ?? 0) + ($ticketData['child'] ?? 0);
        }
        return $total;
    }
}
