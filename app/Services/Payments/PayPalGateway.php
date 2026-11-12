<?php

namespace App\Services\Payments;

use App\Exceptions\PaymentException;
use App\Models\Donation;
use App\Services\Payments\Contracts\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * PayPal Orders v2 (redirect approval flow).
 */
class PayPalGateway implements PaymentGateway
{
    public function method(): string
    {
        return 'paypal';
    }

    public function isConfigured(): bool
    {
        return filled(config('services.paypal.client_id'))
            && filled(config('services.paypal.client_secret'));
    }

    public function initiate(Donation $donation): array
    {
        if (! $this->isConfigured()) {
            throw PaymentException::notConfigured($this->method());
        }

        $order = $this->request('post', '/v2/checkout/orders', token: $this->accessToken(), body: [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $donation->reference,
                'custom_id' => $donation->reference,
                'description' => mb_substr('HopeRise donation '.$donation->reference, 0, 120),
                'amount' => [
                    'currency_code' => $donation->currency,
                    'value' => number_format($donation->amount, 2, '.', ''),
                ],
            ]],
            'application_context' => [
                'brand_name' => config('app.name'),
                'user_action' => 'PAY_NOW',
                'return_url' => $this->returnUrl($donation),
                'cancel_url' => $this->returnUrl($donation, cancelled: true),
            ],
        ]);

        $approve = collect($order['links'] ?? [])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (! is_string($approve)) {
            throw new PaymentException('PayPal did not return an approval link.', status: 502, context: $order);
        }

        return [
            'status' => Donation::STATUS_PENDING,
            'redirect_url' => $approve,
            'provider_reference' => $order['id'] ?? null,
            'meta' => $order,
        ];
    }

    public function confirm(array $payload): ?array
    {
        $resource = is_array($payload['resource'] ?? null) ? $payload['resource'] : [];

        $reference = $resource['custom_id']
            ?? $resource['purchase_units'][0]['custom_id']
            ?? null;

        if (! is_string($reference)) {
            return null;
        }

        $status = match ($payload['event_type'] ?? null) {
            'PAYMENT.CAPTURE.COMPLETED', 'CHECKOUT.ORDER.APPROVED' => Donation::STATUS_PAID,
            'PAYMENT.CAPTURE.DENIED' => Donation::STATUS_FAILED,
            default => null,
        };

        if ($status === null) {
            return null;
        }

        return [
            'reference' => $reference,
            'status' => $status,
            'provider_reference' => is_string($resource['id'] ?? null) ? $resource['id'] : null,
        ];
    }

    /**
     * PayPal verifies the webhook signature through its own API.
     */
    public function verifySignature(Request $request): bool
    {
        $transmission = [
            'auth_algo' => $request->header('paypal-auth-algo'),
            'cert_url' => $request->header('paypal-cert-url'),
            'transmission_id' => $request->header('paypal-transmission-id'),
            'transmission_sig' => $request->header('paypal-transmission-sig'),
            'transmission_time' => $request->header('paypal-transmission-time'),
        ];

        if (in_array(null, $transmission, true) || ! filled(config('services.paypal.webhook_id'))) {
            return false;
        }

        try {
            $response = Http::withToken($this->accessToken())
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post($this->url('/v1/notifications/verify-webhook-signature'), [
                    'transmission_id' => $transmission['transmission_id'],
                    'transmission_time' => $transmission['transmission_time'],
                    'cert_url' => $transmission['cert_url'],
                    'auth_algo' => $transmission['auth_algo'],
                    'transmission_sig' => $transmission['transmission_sig'],
                    'webhook_id' => config('services.paypal.webhook_id'),
                    'webhook_event' => $request->all(),
                ]);
        } catch (Throwable) {
            return false;
        }

        return ($response->json('verification_status') ?? null) === 'SUCCESS';
    }

    private function accessToken(): string
    {
        $response = Http::asForm()
            ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.client_secret'))
            ->timeout(20)
            ->post($this->url('/v1/oauth2/token'), ['grant_type' => 'client_credentials']);

        if ($response->failed() || ! is_string($response->json('access_token'))) {
            throw new PaymentException('PayPal did not return an access token.', status: 502, context: (array) $response->json());
        }

        return (string) $response->json('access_token');
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function request(string $verb, string $path, ?string $token = null, array $body = []): array
    {
        $pending = Http::acceptJson()->asJson()->timeout(20);

        if ($token !== null) {
            $pending = $pending->withToken($token);
        }

        try {
            $response = $verb === 'get'
                ? $pending->get($this->url($path))
                : $pending->post($this->url($path), $body);
        } catch (Throwable $e) {
            throw new PaymentException('PayPal is unreachable: '.$e->getMessage(), status: 502, previous: $e);
        }

        if ($response->failed()) {
            throw new PaymentException(
                (string) ($response->json('message') ?? 'PayPal returned an error.'),
                status: 502,
                context: (array) $response->json(),
            );
        }

        return (array) $response->json();
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.paypal.base_url'), '/').'/'.ltrim($path, '/');
    }

    private function returnUrl(Donation $donation, bool $cancelled = false): string
    {
        $base = rtrim((string) (config('services.paypal.return_url') ?: config('hoperise.frontend_url')), '/');

        return $base.'/donation/'.($cancelled ? 'failed' : 'success').'?reference='.$donation->reference;
    }
}