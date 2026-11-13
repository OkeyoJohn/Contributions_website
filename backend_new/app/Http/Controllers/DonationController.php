<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonationRequest;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use App\Services\MpesaService;
use App\Services\PayPalService;
use App\Services\PaymentSync;
use App\Services\PaystackService;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    public function store(StoreDonationRequest $request, MpesaService $mpesa, PaystackService $paystack, PayPalService $paypal)
    {
        $data = $request->validated();
        $donation = Donation::create([
            'reference' => 'HR-' . strtoupper(Str::random(8)),
            'campaign_id' => $data['campaign_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'amount' => $data['amount'],
            'currency' => 'KES',
            'anonymous' => $data['anonymous'] ?? false,
            'message' => $data['message'] ?? null,
            'payment_method' => $data['payment_method'],
            'status' => 'pending',
        ]);

        $redirect = null;
        try {
            switch ($donation->payment_method) {
                case 'mpesa':
                    $donation->update(['provider_reference' => $mpesa->stkPush($donation)]);
                    break;
                case 'paystack':
                    [$redirect, $code] = $paystack->initialize($donation);
                    $donation->update(['provider_reference' => $code]);
                    break;
                case 'paypal':
                    [$redirect, $orderId, $usd] = $paypal->createOrder($donation);
                    $donation->update(['provider_reference' => $orderId, 'provider_payload' => ['usd' => $usd]]);
                    break;
            }
        } catch (\Throwable $e) {
            report($e);
            $donation->markFailed(['reason' => 'initiation_failed']);
            return response()->json(['message' => 'We could not start the payment. Please try again or choose another method.'], 502);
        }

        return response()->json(['data' => ['reference' => $donation->reference, 'redirect_url' => $redirect]], 201);
    }

    public function show(string $reference, PaymentSync $sync)
    {
        $donation = Donation::with('campaign')->where('reference', $reference)->firstOrFail();
        return new DonationResource($sync->sync($donation));
    }
}
