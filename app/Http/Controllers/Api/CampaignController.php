<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignController extends Controller
{
    /**
     * List the active campaigns shown on the public site.
     */
    public function index(): AnonymousResourceCollection
    {
        return CampaignResource::collection(
            Campaign::query()->where('is_active', true)->orderBy('id')->get()
        );
    }

    /**
     * Show a single campaign by its slug.
     */
    public function show(string $slug): CampaignResource
    {
        $campaign = Campaign::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return new CampaignResource($campaign);
    }
}