<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Http;

class PaystackService
{
    private function http() { return Http::withToken(config('hoperise.paystack.secret'))->baseUrl('https://api.paystack.co'); }

    /** @return array{0:string,1:string} [authorization_url, access_code] */
    public function initialize(Donation $d): array
    {
        $res = $this->http()->post('/transaction/initialize', [
            'email' => $d->email,
            'amount' => (int) round($d->amount * 100), // minor units
            'currency' => $d->currency,
            'reference' => $d->reference,
            'callback_url' => rtrim(config('hoperise.frontend_url'), '/') . '/donation/success?reference=' . $d->reference,
            'metadata' => ['campaign_id' => $d->campaign_id],
        ])->throw();
        return [$res->json('data.authorization_url'), $res->json('data.access_code')];
    }

    public function verify(string $reference): array
    {
        return $this->http()->get('/transaction/verify/' . $reference)->throw()->json('data') ?? [];
    }

    public function validSignature(string $body, ?string $signature): bool
    {
        return $signature && hash_equals(hash_hmac('sha512', $body, (string) config('hoperise.paystack.secret')), $signature);
    }
}
