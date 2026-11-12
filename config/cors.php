<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| The React frontend in frontend/ runs on its own Vite dev server, so it must
| be allowed to call /api/*. Localhost origins are matched by pattern so any
| Vite port (5173, 5174, ...) works; set CORS_ALLOWED_ORIGINS for other hosts.
|
*/

$allowedOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')),
)));

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => ['#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];