<?php

namespace App\Http\Controllers;

use App\Http\Resources\CampaignResource;
use App\Models\Campaign;

class CampaignController extends Controller
{
    public function index()
    {
        return CampaignResource::collection(Campaign::where('is_active', true)->latest()->get());
    }

    public function show(string $slug)
    {
        return new CampaignResource(Campaign::where('is_active', true)->where('slug', $slug)->firstOrFail());
    }
}
