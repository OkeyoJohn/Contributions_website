<?php

namespace App\Services\Payments;

use App\Exceptions\PaymentException;
use App\Models\Donation;
use App\Services\Payments\Contracts\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Paystack hosted checkout (Visa / Mastercard).
 */
class PaystackGateway implements PaymentGateway
{
    public function method(): string
    {
        return 'paystack';
    }

    public function isConfigured(): bool
    {
        return filled(config('services.paystack.secret_key'));
    }

    public function initiate(Donation $donation): array
    {
        if (! $this->isConfigured()) {
            throw PaymentException::notConfigured($this->method());
        }

        $response = Http::withToken(config('services.paystack.secret_key'))
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->post($this->url('/transaction/initialize'), [
                'email' => $donation->email,
                'amount' => $donation->amount * 100, // Paystack charges in the currency subunit (kobo/cents).
                'currency' => $donation->currency,
                'reference' => $donation->reference,
                'callback_url' => $this->callbackUrl(),
                'metadata' => [
                    'donation_reference' => $donation->reference,
                    'campaign_id' => $donation->campaign_id,
                    'donor_name' => $donation->name,
                ],
            ]);

        if ($response->failed()) {
            throw new PaymentException(
                (string) ($response->json('message') ?? 'Paystack could not start the payment.'),
                status: 502,
                context: (array) $response->json(),
            );
        }

        if (! ($response->json('status') ?? false)) {
            throw new PaymentException(
                (string) ($response->json('message') ?? 'Paystack could not start the payment.'),
                status: 422,
                context: (array) $response->json(),
            );
        }

        return [
            'status' => Donation::STATUS_PENDING,
            'redirect_url' => $response->json('data.authorization_url'),
            'provider_reference' => $response->json('data.reference') ?? $donation->reference,
            'meta' => (array) $response->json('data'),
        ];
    }

    public function confirm(array $payload): ?array
    {
        $data = $payload['data'] ?? null;
        $reference = is_array($data) ? ($data['reference'] ?? null) : null;

        if (! is_array($data) || ! is_string($reference)) {
            return null;
        }

        $status = match ($data['status'] ?? null) {
            'success' => Donation::STATUS_PAID,
            'failed' => Donation::STATUS_FAILED,
            default => null,
        };

        if ($status === null) {
            return null;
        }

        return [
            'reference' => $reference,
            'status' => $status,
            'provider_reference' => $reference,
        ];
    }

    /**
     * Paystack signs the raw request body with HMAC-SHA512.
     */
    public function verifySignature(Request $request): bool
    {
        $signature = (string) $request->header('x-paystack-signature', '');
        $expected = hash_hmac('sha512', $request->getContent(), (string) config('services.paystack.secret_key'));

        return $signature !== '' && hash_equals($expected, $signature);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.paystack.base_url'), '/').'/'.ltrim($path, '/');
    }

    private function callbackUrl(): string
    {
        $configured = config('services.paystack.callback_url');

        if ($configured) {
            return (string) $configured;
        }

        return rtrim((string) config('hoperise.frontend_url'), '/').'/donation/success';
    }
}