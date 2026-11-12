<?php

namespace App\Services\Payments\Contracts;

use App\Models\Donation;
use Illuminate\Http\Request;

interface PaymentGateway
{
    /**
     * The payment method handled by this gateway (matches Donation::PAYMENT_METHODS).
     */
    public function method(): string;

    /**
     * Whether the provider credentials needed to charge a donor are present.
     */
    public function isConfigured(): bool;

    /**
     * Verify that a provider webhook/callback really came from the provider.
     */
    public function verifySignature(Request $request): bool;

    /**
     * Start the payment for a donation.
     *
     * @return array{status: string, redirect_url: string|null, provider_reference: string|null, meta: array<string, mixed>}
     */
    public function initiate(Donation $donation): array;

    /**
     * Apply a provider webhook/callback. Return null when the payload is not for us.
     *
     * @param  array<string, mixed>  $payload
     * @return array{reference: string, status: string, provider_reference: string|null}|null
     */
    public function confirm(array $payload): ?array;
}