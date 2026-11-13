<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** Public view: no email, phone or name. */
class DonationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'reference' => $this->reference,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'campaign' => $this->campaign?->title,
            'paid_at' => $this->paid_at,
        ];
    }
}
