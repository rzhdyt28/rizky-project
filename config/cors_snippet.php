<?php
// // GANTIKAN isi config/cors.php dengan konfigurasi ini (untuk Vue terpisah):
// return [
//     'paths' => ['api/*', 'sanctum/csrf-cookie'],
//     'allowed_methods' => ['*'],
//     // Frontend Vue: dev = http://localhost:5173, prod = https://rizky.com dst.
//     'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
//     'allowed_origins_patterns' => [
//         // izinkan seluruh subdomain tenant, mis. https://reza-mega.rizky.com
//         '#^https?://([a-z0-9-]+\.)?rizky\.(com|test)(:\d+)?$#',
//     ],
//     'allowed_headers' => ['*'],
//     'exposed_headers' => [],
//     'max_age' => 0,
//     'supports_credentials' => true,   // WAJIB true untuk Sanctum cookie
// ];

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],

    // Origin eksplisit untuk PRODUKSI (diisi dari .env, contoh: https://rizky.com).
    // localhost tetap ikut di sini supaya dev di laptop sendiri tetap jalan
    // tanpa perlu jaringan LAN sama sekali.
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [
        // Subdomain tenant produksi, mis. https://reza-mega.rizky.com
        '#^https?://([a-z0-9-]+\.)?rizky\.(com|test)(:\d+)?$#',

        // SEMUA IP jaringan lokal (LAN) port 5173 — mengizinkan akses dari HP
        // manapun di WiFi yang sama TANPA perlu update .env tiap kali IP
        // laptop berubah (DHCP). Mencakup 3 rentang IP privat standar:
        //   192.168.x.x , 10.x.x.x , 172.16.x.x–172.31.x.x
        '#^http://192\.168\.\d{1,3}\.\d{1,3}:5173$#',
        '#^http://10\.\d{1,3}\.\d{1,3}\.\d{1,3}:5173$#',
        '#^http://172\.(1[6-9]|2\d|3[01])\.\d{1,3}\.\d{1,3}:5173$#',
    ],

    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // WAJIB true untuk Sanctum cookie
];