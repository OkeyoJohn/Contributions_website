<?php

return [
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),

    'mpesa' => [
        'env' => env('MPESA_ENV', 'sandbox'), // sandbox | production
        'key' => env('MPESA_CONSUMER_KEY'),
        'secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        'callback_secret' => env('MPESA_CALLBACK_SECRET'),
        'callback_base' => env('MPESA_CALLBACK_BASE_URL'), // public HTTPS URL of this API (use ngrok locally)
    ],

    'paystack' => [
        'secret' => env('PAYSTACK_SECRET_KEY'),
    ],

    'paypal' => [
        'env' => env('PAYPAL_ENV', 'sandbox'), // sandbox | live
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'kes_per_usd' => (float) env('PAYPAL_KES_PER_USD', 129), // PayPal does not support KES
    ],
];
