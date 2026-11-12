<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PayPalService
{
    private function base(): string
    {
        return config('hoperise.paypal.env') === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private function http()
    {
        $token = Cache::remember('paypal_token', 1800, fn () => Http::asForm()
            ->withBasicAuth(config('hoperise.paypal.client_id'), config('hoperise.paypal.secret'))
            ->post($this->base() . '/v1/oauth2/token', ['grant_type' => 'client_credentials'])->throw()->json('access_token'));
        return Http::withToken($token)->baseUrl($this->base());
    }

    /** PayPal does not support KES, so the KES amount is converted to USD using a configured rate. */
    public function toUsd(float $kes): string
    {
        return number_format(max(0.01, round($kes / config('hoperise.paypal.kes_per_usd'), 2)), 2, '.', '');
    }

    /** @return array{0:string,1:string,2:string} [approval_url, order_id, usd_amount] */
    public function createOrder(Donation $d): array
    {
        $usd = $this->toUsd((float) $d->amount);
        $res = $this->http()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $d->reference,
                'custom_id' => $d->reference,
                'description' => 'HopeRise donation',
                'amount' => ['currency_code' => 'USD', 'value' => $usd],
            ]],
            'payment_source' => ['paypal' => ['experience_context' => [
                'user_action' => 'PAY_NOW',
                'return_url' => url('/api/paypal/return?reference=' . $d->reference),
                'cancel_url' => rtrim(config('hoperise.frontend_url'), '/') . '/donation/failed',
            ]]],
        ])->throw();

        $link = collect($res->json('links'))->first(fn ($l) => in_array($l['rel'], ['payer-action', 'approve']));
        return [$link['href'], $res->json('id'), $usd];
    }

    public function capture(string $orderId): array
    {
        return $this->http()->withBody('{}', 'application/json')->post("/v2/checkout/orders/$orderId/capture")->throw()->json();
    }
}
