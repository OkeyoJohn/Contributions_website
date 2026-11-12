<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray($request): array
    {
        return $this->only(['id', 'slug', 'title', 'description', 'long_description', 'image_url', 'goal_amount', 'raised_amount']);
    }
}
