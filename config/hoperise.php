<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Frontend URL
    |--------------------------------------------------------------------------
    |
    | Where the React frontend is served from. Payment providers redirect the
    | donor back here after checkout (see services.php payment callbacks).
    |
    */
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),

    /*
    |--------------------------------------------------------------------------
    | Payment simulation
    |--------------------------------------------------------------------------
    |
    | true  - always confirm donations without contacting a provider
    | false - never simulate; missing provider credentials are an error
    | null  - simulate only when the provider has no credentials and the app
    |         is not running in production
    |
    */
    'payments' => [
        'simulate' => env('PAYMENTS_SIMULATE') === null
            ? null
            : filter_var(env('PAYMENTS_SIMULATE'), FILTER_VALIDATE_BOOLEAN),
    ],

];