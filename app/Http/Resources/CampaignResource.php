<?php

namespace App\Http\Resources;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Campaign
 */
class CampaignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'long_description' => $this->long_description,
            'goal_amount' => $this->goal_amount,
            'raised_amount' => $this->raised_amount,
            'image_url' => $this->image_url,
            'progress' => $this->goal_amount > 0
                ? min(100, (int) round($this->raised_amount / $this->goal_amount * 100))
                : 0,
        ];
    }
}