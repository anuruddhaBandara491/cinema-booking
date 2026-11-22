<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        $this->booking->loadMissing('movie');
    }

    public function envelope(): Envelope
    {
        $title = $this->booking->movie?->title ?? 'your show';

        return new Envelope(
            subject: 'Your ticket for '.$title.' — '.$this->booking->booking_reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-ticket',
            with: [
                'cinemaName' => config('cinema.business.name'),
                'booking' => $this->booking,
            ],
        );
    }
}
