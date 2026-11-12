<?php

namespace App\Services\Payments;

use App\Models\Donation;
use App\Services\Payments\Contracts\PaymentGateway;
use Illuminate\Http\Request;

/**
 * Marks a donation as paid immediately, without contacting a provider.
 *
 * Used for local development and automated tests, so the donation flow can be
 * exercised end to end before M-Pesa, Paystack and PayPal credentials exist.
 */
class SimulatedGateway implements PaymentGateway
{
    public function method(): string
    {
        return 'simulated';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function initiate(Donation $donation): array
    {
        return [
            'status' => Donation::STATUS_PAID,
            'redirect_url' => null,
            'provider_reference' => 'SIM-'.$donation->reference,
            'meta' => ['simulated' => true],
        ];
    }

    public function confirm(array $payload): ?array
    {
        return null;
    }

    public function verifySignature(Request $request): bool
    {
        return true;
    }
}