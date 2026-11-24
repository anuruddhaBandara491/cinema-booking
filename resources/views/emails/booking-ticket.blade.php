<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your ticket {{ $booking->booking_reference }}</title>
</head>
<body style="margin:0;padding:24px;background:#f4f4f5;color:#111827;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;">
        <tr>
            <td style="padding:28px 28px 8px;">
                <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#6b7280;">{{ $cinemaName }}</p>
                <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;">Your ticket is confirmed</h1>
                <p style="margin:12px 0 0;font-size:14px;line-height:1.5;color:#374151;">
                    Hello {{ $booking->customer_name }}, show this email or a printed copy at the door.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:8px 28px 28px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:14px 0 4px;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:#6b7280;">Booking reference</td>
                    </tr>
                    <tr>
                        <td style="padding:0 0 14px;font-size:22px;font-weight:700;letter-spacing:0.04em;">{{ $booking->booking_reference }}</td>
                    </tr>
                    <tr>
                        <td style="padding:14px 0 4px;border-top:1px solid #e5e7eb;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:#6b7280;">Film</td>
                    </tr>
                    <tr>
                        <td style="padding:0 0 14px;font-size:16px;">{{ $booking->movie?->title ?? 'Show' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:14px 0 4px;border-top:1px solid #e5e7eb;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:#6b7280;">Date</td>
                    </tr>
                    <tr>
                        <td style="padding:0 0 14px;font-size:16px;">{{ $booking->booking_date?->format('l, d M Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:14px 0 4px;border-top:1px solid #e5e7eb;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:#6b7280;">Time</td>
                    </tr>
                    <tr>
                        <td style="padding:0 0 14px;font-size:16px;">{{ $booking->booking_time }}</td>
                    </tr>
                    <tr>
                        <td style="padding:14px 0 4px;border-top:1px solid #e5e7eb;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:#6b7280;">Seats</td>
                    </tr>
                    <tr>
                        <td style="padding:0 0 14px;font-size:16px;">{{ implode(', ', $booking->selected_seats ?? []) }}</td>
                    </tr>
                </table>
                <p style="margin:8px 0 0;font-size:13px;line-height:1.5;color:#4b5563;">
                    Ticket price: LKR {{ number_format($ticketPrice, 2) }}. Arrive at least 30 minutes before the show.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
