<?php

namespace App\Services;

use App\Models\Booking;

class PayhereService
{
    public function isConfigured(): bool
    {
        return $this->merchantId() !== '' && $this->merchantSecret() !== '';
    }

    public function isSandbox(): bool
    {
        return (bool) config('payhere.sandbox');
    }

    public function checkoutUrl(): string
    {
        return $this->isSandbox()
            ? 'https://sandbox.payhere.lk/pay/checkout'
            : 'https://www.payhere.lk/pay/checkout';
    }

    public function formatAmount(float|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    public function checkoutHash(string $orderId, string $amount): string
    {
        return strtoupper(md5(
            $this->merchantId()
            . $orderId
            . $amount
            . $this->currency()
            . strtoupper(md5($this->merchantSecret()))
        ));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verifyNotification(array $payload): bool
    {
        $signature = strtoupper((string) ($payload['md5sig'] ?? ''));

        if ($signature === '') {
            return false;
        }

        $localSignature = strtoupper(md5(
            (string) ($payload['merchant_id'] ?? '')
            . (string) ($payload['order_id'] ?? '')
            . (string) ($payload['payhere_amount'] ?? '')
            . (string) ($payload['payhere_currency'] ?? '')
            . (string) ($payload['status_code'] ?? '')
            . strtoupper(md5($this->merchantSecret()))
        ));

        return hash_equals($localSignature, $signature);
    }

    /**
     * @return array<string, string>
     */
    public function checkoutFields(Booking $booking): array
    {
        $booking->loadMissing('movie');

        [$firstName, $lastName] = $this->splitCustomerName($booking->customer_name);
        $amount = $this->formatAmount($booking->total_amount);
        $orderId = $booking->booking_reference;

        return [
            'merchant_id' => $this->merchantId(),
            'return_url' => route('bookings.payhere.return', $booking),
            'cancel_url' => route('bookings.payhere.cancel', $booking),
            'notify_url' => route('payhere.notify'),
            'order_id' => $orderId,
            'items' => $this->itemTitle($booking),
            'currency' => $this->currency(),
            'amount' => $amount,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => (string) $booking->customer_email,
            'phone' => (string) $booking->customer_phone,
            'address' => (string) config('cinema.business.address'),
            'city' => (string) config('payhere.city'),
            'country' => (string) config('cinema.business.country', 'Sri Lanka'),
            'custom_1' => (string) $booking->session_id,
            'custom_2' => (string) $booking->id,
            'hash' => $this->checkoutHash($orderId, $amount),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function splitCustomerName(string $fullName): array
    {
        $fullName = trim(preg_replace('/\s+/', ' ', $fullName) ?? '');
        $parts = $fullName === '' ? [] : explode(' ', $fullName, 2);
        $firstName = $parts[0] ?? 'Customer';
        $lastName = $parts[1] ?? $firstName;

        return [$firstName, $lastName];
    }

    private function itemTitle(Booking $booking): string
    {
        $title = trim((string) ($booking->movie->title ?? 'Cinema ticket'));
        $title = preg_replace('/[^\p{L}\p{N}\s\-\.\,]/u', '', $title) ?? 'Cinema ticket';

        return mb_substr($title === '' ? 'Cinema ticket' : $title, 0, 100);
    }

    private function merchantId(): string
    {
        return (string) config('payhere.merchant_id');
    }

    private function merchantSecret(): string
    {
        return (string) config('payhere.merchant_secret');
    }

    private function currency(): string
    {
        return (string) config('payhere.currency', 'LKR');
    }
}
