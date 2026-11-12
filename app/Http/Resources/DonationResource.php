<?php

namespace App\Http\Resources;

use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Donation
 */
class DonationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'reference' => $this->reference,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'anonymous' => $this->anonymous,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
        ];
    }
}