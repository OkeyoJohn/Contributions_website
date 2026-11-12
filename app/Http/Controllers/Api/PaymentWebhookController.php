<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\DonationService;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayFactory $gateways,
        private readonly DonationService $donations,
    ) {}

    /**
     * Provider callbacks that confirm (or fail) a donation.
     */
    public function __invoke(Request $request, string $provider): JsonResponse
    {
        try {
            $gateway = $this->gateways->exact($provider);
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'Unknown payment provider.'], 404);
        }

        if (! $gateway->isConfigured()) {
            return response()->json(['message' => 'This payment provider is not configured.'], 503);
        }

        if (! $gateway->verifySignature($request)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $result = $gateway->confirm($request->all());

        if ($result === null) {
            return response()->json(['message' => 'No action needed.']);
        }

        $donation = Donation::query()->where('reference', $result['reference'])->first();

        if (! $donation) {
            return response()->json(['message' => 'Unknown donation.'], 404);
        }

        $this->donations->applyStatus($donation, $result['status'], $result['provider_reference']);

        return response()->json(['message' => 'Payment processed.']);
    }
}