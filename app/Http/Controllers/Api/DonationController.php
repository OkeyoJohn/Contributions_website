<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDonationRequest;
use App\Http\Resources\DonationResource;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;

class DonationController extends Controller
{
    public function __construct(private readonly DonationService $donations) {}

    /**
     * Record a donation and start the payment with the chosen provider.
     */
    public function store(StoreDonationRequest $request): JsonResponse
    {
        try {
            $result = $this->donations->create($request->validated());
        } catch (PaymentException $e) {
            return response()->json(
                array_merge(['message' => $e->getMessage()], $e->context),
                $e->status,
            );
        }

        $donation = $result['donation'];

        return response()->json([
            'data' => [
                'reference' => $donation->reference,
                'status' => $donation->status,
                'amount' => $donation->amount,
                'currency' => $donation->currency,
                'payment_method' => $donation->payment_method,
                'redirect_url' => $result['redirect_url'],
            ],
        ], 201);
    }

    /**
     * The verified status of a donation. Polled by the success page.
     */
    public function show(string $reference): DonationResource
    {
        $donation = $this->donations->findByReference($reference);

        abort_if($donation === null, 404, 'Donation not found.');

        return new DonationResource($donation);
    }
}