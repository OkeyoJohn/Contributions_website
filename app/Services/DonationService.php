<?php

namespace App\Services;

use App\Exceptions\PaymentException;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class DonationService
{
    public function __construct(private readonly PaymentGatewayFactory $gateways) {}

    /**
     * Record a donation and start the payment with its provider.
     *
     * @param  array<string, mixed>  $data
     * @return array{donation: Donation, redirect_url: string|null}
     */
    public function create(array $data): array
    {
        $donation = Donation::create([
            'reference' => $this->reference(),
            'campaign_id' => $data['campaign_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'amount' => $data['amount'],
            'currency' => strtoupper((string) ($data['currency'] ?? 'KES')),
            'payment_method' => $data['payment_method'],
            'anonymous' => (bool) ($data['anonymous'] ?? false),
            'message' => $data['message'] ?? null,
            'status' => Donation::STATUS_PENDING,
        ]);

        $gateway = $this->gateways->for($donation->payment_method);

        try {
            $result = $gateway->initiate($donation);
        } catch (PaymentException $e) {
            $donation->update(['status' => Donation::STATUS_FAILED, 'meta' => $e->context]);

            throw $e;
        } catch (Throwable $e) {
            $donation->update([
                'status' => Donation::STATUS_FAILED,
                'meta' => ['error' => $e->getMessage()],
            ]);

            throw new PaymentException('We could not start your payment. Please try again.', status: 502, previous: $e);
        }

        $this->applyStatus($donation, $result['status'], $result['provider_reference'], $result['meta']);

        return [
            'donation' => $donation->load('campaign'),
            'redirect_url' => $result['redirect_url'],
        ];
    }

    /**
     * Move a donation to the status reported by the provider and keep the
     * campaign totals in step. Safe to call repeatedly (webhooks retry).
     *
     * @param  array<string, mixed>  $meta
     */
    public function applyStatus(
        Donation $donation,
        string $status,
        ?string $providerReference = null,
        array $meta = [],
    ): void {
        if (! in_array($status, [Donation::STATUS_PENDING, Donation::STATUS_PAID, Donation::STATUS_FAILED], true)) {
            return;
        }

        DB::transaction(function () use ($donation, $status, $providerReference, $meta): void {
            $fresh = Donation::query()->lockForUpdate()->find($donation->id);

            if (! $fresh) {
                return;
            }

            $wasPaid = $fresh->status === Donation::STATUS_PAID;

            $attributes = ['status' => $status];

            if ($providerReference !== null) {
                $attributes['provider_reference'] = $providerReference;
            }

            if ($status === Donation::STATUS_PAID) {
                $attributes['paid_at'] = $fresh->paid_at ?? now();
            }

            if ($meta !== []) {
                $attributes['meta'] = $meta;
            }

            $fresh->update($attributes);

            if ($status === Donation::STATUS_PAID && ! $wasPaid && $fresh->campaign_id) {
                Campaign::query()->whereKey($fresh->campaign_id)->increment('raised_amount', $fresh->amount);
            }
        });

        $donation->refresh();
    }

    public function findByReference(string $reference): ?Donation
    {
        return Donation::query()->with('campaign')->where('reference', $reference)->first();
    }

    private function reference(): string
    {
        do {
            $reference = 'HR-'.strtoupper(Str::random(12));
        } while (Donation::query()->where('reference', $reference)->exists());

        return $reference;
    }
}