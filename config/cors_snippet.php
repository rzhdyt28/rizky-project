<?php
// GANTIKAN isi config/cors.php dengan konfigurasi ini (untuk Vue terpisah):
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    // Frontend Vue: dev = http://localhost:5173, prod = https://rizky.com dst.
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
    'allowed_origins_patterns' => [
        // izinkan seluruh subdomain tenant, mis. https://reza-mega.rizky.com
        '#^https?://([a-z0-9-]+\.)?rizky\.(com|test)(:\d+)?$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,   // WAJIB true untuk Sanctum cookie
];
