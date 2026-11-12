<?php

namespace App\Services;

use App\Models\Donation;

/**
 * Turns provider responses into a verified donation status.
 * Used by both webhooks and the status endpoint, so the result never depends on the browser.
 */
class PaymentSync
{
    public function __construct(private PaystackService $paystack, private MpesaService $mpesa) {}

    public function sync(Donation $d): Donation
    {
        if ($d->status !== 'pending') return $d;
        try {
            match ($d->payment_method) {
                'paystack' => $this->paystack($d),
                'mpesa' => $this->mpesa($d),
                default => null, // PayPal is confirmed on its return route
            };
        } catch (\Throwable $e) {
            report($e); // leave pending; we will try again
        }
        return $d->refresh();
    }

    private function paystack(Donation $d): void
    {
        $tx = $this->paystack->verify($d->reference);
        $status = $tx['status'] ?? null;
        if ($status === 'success') {
            $amountOk = (int) ($tx['amount'] ?? 0) === (int) round($d->amount * 100);
            $currencyOk = ($tx['currency'] ?? '') === $d->currency;
            $amountOk && $currencyOk ? $d->markPaid($tx) : $d->markFailed(['reason' => 'amount_mismatch', 'tx' => $tx]);
        } elseif ($status === 'failed') {
            $d->markFailed($tx);
        }
    }

    private function mpesa(Donation $d): void
    {
        if (! $d->provider_reference) return;
        $r = $this->mpesa->query($d->provider_reference);
        if ($r['code'] === null) return;             // still processing
        $r['code'] === 0 ? $d->markPaid($r['raw']) : $d->markFailed($r['raw']); // 1032 = cancelled, 1 = insufficient funds, etc.
    }
}
