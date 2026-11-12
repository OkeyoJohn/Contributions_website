<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('email');
            $t->string('phone')->nullable();
            $t->decimal('amount', 12, 2);
            $t->string('currency', 3)->default('KES');
            $t->boolean('anonymous')->default(false);
            $t->text('message')->nullable();
            $t->string('payment_method'); // mpesa | paystack | paypal
            $t->string('status')->default('pending')->index(); // pending | paid | failed
            $t->string('provider_reference')->nullable()->index(); // CheckoutRequestID / Paystack access code / PayPal order id
            $t->json('provider_payload')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('donations'); }
};
