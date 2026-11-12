<?php

namespace App\Services\Payments;

use App\Exceptions\PaymentException;
use App\Models\Donation;
use App\Services\Payments\Contracts\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Safaricom Daraja M-Pesa Express (STK Push).
 *
 * The donor approves the payment on their phone; the result arrives later on the
 * callback URL and is applied by PaymentWebhookController.
 */
class MpesaGateway implements PaymentGateway
{
    public function method(): string
    {
        return 'mpesa';
    }

    public function isConfigured(): bool
    {
        return filled(config('services.mpesa.consumer_key'))
            && filled(config('services.mpesa.consumer_secret'))
            && filled(config('services.mpesa.shortcode'))
            && filled(config('services.mpesa.passkey'));
    }

    public function initiate(Donation $donation): array
    {
        if (! $this->isConfigured()) {
            throw PaymentException::notConfigured($this->method());
        }

        $timestamp = now()->format('YmdHis');
        $shortcode = (string) config('services.mpesa.shortcode');
        $password = base64_encode($shortcode.(string) config('services.mpesa.passkey').$timestamp);

        $data = $this->request('post', '/mpesa/stkpush/v1/processrequest', token: $this->accessToken(), body: [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $donation->amount,
            'PartyA' => $this->msisdn($donation->phone),
            'PartyB' => $shortcode,
            'PhoneNumber' => $this->msisdn($donation->phone),
            'CallBackURL' => $this->callbackUrl(),
            'AccountReference' => $donation->reference,
            'TransactionDesc' => 'HopeRise donation '.$donation->reference,
        ]);

        if ((int) ($data['ResponseCode'] ?? 1) !== 0) {
            throw new PaymentException(
                (string) ($data['errorMessage'] ?? 'M-Pesa rejected the payment request.'),
                status: 422,
                context: $data,
            );
        }

        return [
            'status' => Donation::STATUS_PENDING,
            'redirect_url' => null,
            'provider_reference' => $data['CheckoutRequestID'] ?? null,
            'meta' => $data,
        ];
    }

    public function confirm(array $payload): ?array
    {
        $callback = $payload['Body']['stkCallback'] ?? null;
        $reference = $callback['CheckoutRequestID'] ?? null;

        if (! is_array($callback) || ! is_string($reference)) {
            return null;
        }

        $donation = Donation::query()->where('provider_reference', $reference)->first();

        if (! $donation) {
            return null;
        }

        return [
            'reference' => $donation->reference,
            'status' => (int) ($callback['ResultCode'] ?? 1) === 0
                ? Donation::STATUS_PAID
                : Donation::STATUS_FAILED,
            'provider_reference' => $reference,
        ];
    }

    /**
     * Daraja callbacks are not signed; authenticity is enforced by keeping the
     * callback URL secret and by checking the payload against the stored donation.
     */
    public function verifySignature(Request $request): bool
    {
        return true;
    }

    private function accessToken(): string
    {
        $data = $this->request('get', '/oauth/v1/generate?grant_type=client_credentials');

        if (! is_string($data['access_token'] ?? null)) {
            throw new PaymentException('M-Pesa did not return an access token.', status: 502, context: $data);
        }

        return $data['access_token'];
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function request(string $verb, string $path, ?string $token = null, array $body = []): array
    {
        $pending = Http::acceptJson()->asJson()->timeout(20);

        $pending = $token === null
            ? $pending->withBasicAuth(config('services.mpesa.consumer_key'), config('services.mpesa.consumer_secret'))
            : $pending->withToken($token);

        try {
            $response = $verb === 'get'
                ? $pending->get($this->url($path))
                : $pending->post($this->url($path), $body);
        } catch (Throwable $e) {
            throw new PaymentException('M-Pesa is unreachable: '.$e->getMessage(), status: 502, previous: $e);
        }

        if ($response->failed()) {
            throw new PaymentException(
                (string) ($response->json('errorMessage') ?? 'M-Pesa returned an error.'),
                status: 502,
                context: (array) $response->json(),
            );
        }

        return (array) $response->json();
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.mpesa.base_url'), '/').'/'.ltrim($path, '/');
    }

    private function callbackUrl(): string
    {
        $configured = config('services.mpesa.callback_url');

        return $configured
            ? (string) $configured
            : route('api.webhooks.payments', ['provider' => $this->method()]);
    }

    /**
     * Convert a Kenyan phone number to the 2547XXXXXXXX / 2541XXXXXXXX MSISDN format.
     */
    private function msisdn(?string $phone): string
    {
        $digits = (string) preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '254')) {
            return $digits;
        }

        return '254'.ltrim($digits, '0');
    }
}