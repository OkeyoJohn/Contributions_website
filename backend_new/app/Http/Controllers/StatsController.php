<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;

/** Real numbers only, computed from verified (paid) donations. */
class StatsController extends Controller
{
    public function __invoke()
    {
        $paid = Donation::where('status', 'paid');
        return response()->json(['data' => [
            'donations_received' => (int) (clone $paid)->where('currency', 'KES')->sum('amount'),
            'donors' => (clone $paid)->distinct('email')->count('email'),
            'active_campaigns' => Campaign::where('is_active', true)->count(),
        ]]);
    }
}
