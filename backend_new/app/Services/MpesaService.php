<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MpesaService
{
    private function base(): string
    {
        return config('hoperise.mpesa.env') === 'production' ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';
    }

    private function token(): string
    {
        return Cache::remember('mpesa_token', 3000, fn () => Http::withBasicAuth(config('hoperise.mpesa.key'), config('hoperise.mpesa.secret'))
            ->get($this->base() . '/oauth/v1/generate?grant_type=client_credentials')->throw()->json('access_token'));
    }

    public static function normalizePhone(string $phone): string
    {
        $p = preg_replace('/\D/', '', $phone);
        if (str_starts_with($p, '0')) return '254' . substr($p, 1);
        if (strlen($p) === 9) return '254' . $p;
        return $p;
    }

    private function password(string $ts): string
    {
        return base64_encode(config('hoperise.mpesa.shortcode') . config('hoperise.mpesa.passkey') . $ts);
    }

    /** Sends the STK Push to the donor's phone. Returns the CheckoutRequestID. */
    public function stkPush(Donation $d): string
    {
        $ts = now()->format('YmdHis');
        $base = rtrim(config('hoperise.mpesa.callback_base') ?: config('app.url'), '/');
        $res = Http::withToken($this->token())->post($this->base() . '/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => config('hoperise.mpesa.shortcode'),
            'Password' => $this->password($ts),
            'Timestamp' => $ts,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) ceil($d->amount),
            'PartyA' => self::normalizePhone($d->phone),
            'PartyB' => config('hoperise.mpesa.shortcode'),
            'PhoneNumber' => self::normalizePhone($d->phone),
            'CallBackURL' => $base . '/api/webhooks/mpesa/' . config('hoperise.mpesa.callback_secret'),
            'AccountReference' => $d->reference, // max 12 chars
            'TransactionDesc' => 'Donation',
        ])->throw();

        if ($res->json('ResponseCode') !== '0') throw new \RuntimeException('M-Pesa rejected the request: ' . $res->body());
        return $res->json('CheckoutRequestID');
    }

    /** Asks Safaricom for the result. Returns ['code' => int|null, ...]; null code = still processing. */
    public function query(string $checkoutRequestId): array
    {
        $ts = now()->format('YmdHis');
        $res = Http::withToken($this->token())->post($this->base() . '/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => config('hoperise.mpesa.shortcode'),
            'Password' => $this->password($ts),
            'Timestamp' => $ts,
            'CheckoutRequestID' => $checkoutRequestId,
        ]);
        $code = $res->json('ResultCode');
        return ['code' => $code === null ? null : (int) $code, 'raw' => $res->json()];
    }
}
