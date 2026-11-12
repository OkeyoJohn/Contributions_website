<?php

use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API used by the React frontend (frontend/ directory)
|--------------------------------------------------------------------------
|
| Every response is wrapped in { "data": ... } so the frontend's unwrap()
| helper can read it. Amounts are whole Kenyan shillings.
|
*/

Route::get('/campaigns', [CampaignController::class, 'index'])->name('api.campaigns.index');
Route::get('/campaigns/{slug}', [CampaignController::class, 'show'])->name('api.campaigns.show');

Route::post('/donations', [DonationController::class, 'store'])->name('api.donations.store');
Route::get('/donations/{reference}', [DonationController::class, 'show'])->name('api.donations.show');

Route::post('/webhooks/payments/{provider}', PaymentWebhookController::class)
    ->name('api.webhooks.payments')
    ->whereIn('provider', ['mpesa', 'paystack', 'paypal']);