<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\PayPalService;
use App\Services\PaymentSync;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /** Paystack: signature is checked, then the transaction is re-verified with Paystack's API. */
    public function paystack(Request $request, PaystackService $paystack, PaymentSync $sync)
    {
        abort_unless($paystack->validSignature($request->getContent(), $request->header('x-paystack-signature')), 401);

        if ($request->input('event') === 'charge.success') {
            $d = Donation::where('reference', $request->input('data.reference'))->first();
            if ($d) $sync->sync($d);
        }
        return response()->json(['ok' => true]);
    }

    /** M-Pesa: Safaricom does not sign callbacks, so the URL carries a secret and the result is confirmed again by STK query. */
    public function mpesa(Request $request, string $secret, PaymentSync $sync)
    {
        abort_unless(hash_equals((string) config('hoperise.mpesa.callback_secret'), $secret), 404);

        $cb = $request->input('Body.stkCallback', []);
        $d = Donation::where('provider_reference', $cb['CheckoutRequestID'] ?? '')->where('payment_method', 'mpesa')->first();

        if ($d && $d->status === 'pending') {
            if ((int) ($cb['ResultCode'] ?? -1) === 0) {
                $items = collect($cb['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name');
                (float) ($items['Amount'] ?? 0) >= (float) $d->amount
                    ? $d->markPaid($cb)
                    : $d->markFailed(['reason' => 'amount_mismatch', 'callback' => $cb]);
            } else {
                $sync->sync($d); // confirms failure/cancellation via STK query
            }
        }
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /** PayPal return URL: captures the approved order server-side, then sends the donor to the React app. */
    public function paypalReturn(Request $request, PayPalService $paypal)
    {
        $front = rtrim(config('hoperise.frontend_url'), '/');
        $d = Donation::where('reference', $request->query('reference'))->where('payment_method', 'paypal')->first();

        if ($d && $d->status === 'pending' && hash_equals((string) $d->provider_reference, (string) $request->query('token'))) {
            try {
                $res = $paypal->capture($d->provider_reference);
                $cap = $res['purchase_units'][0]['payments']['captures'][0] ?? [];
                $ok = ($res['status'] ?? '') === 'COMPLETED'
                    && ($cap['amount']['currency_code'] ?? '') === 'USD'
                    && ($cap['amount']['value'] ?? '') === ($d->provider_payload['usd'] ?? null);
                $ok ? $d->markPaid($res) : $d->markFailed($res);
            } catch (\Throwable $e) {
                report($e); // stays pending
            }
        }
        return redirect($front . '/donation/success?reference=' . urlencode((string) $request->query('reference')));
    }
}
