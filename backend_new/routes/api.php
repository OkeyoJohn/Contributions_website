<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('campaigns', [CampaignController::class, 'index']);
Route::get('campaigns/{slug}', [CampaignController::class, 'show']);
Route::get('stats', StatsController::class);

Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,1');

Route::post('donations', [DonationController::class, 'store'])->middleware('throttle:10,1');
Route::get('donations/{reference}', [DonationController::class, 'show'])->middleware('throttle:60,1');

Route::post('webhooks/paystack', [WebhookController::class, 'paystack']);
Route::post('webhooks/mpesa/{secret}', [WebhookController::class, 'mpesa']);
Route::get('paypal/return', [WebhookController::class, 'paypalReturn']);
