<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Development lokal
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://172.18.0.1:5173',
        'http://172.16.20.218:5173',
        'http://172.16.20.218:8000',
        // Production Vercel
        'https://sitaradev-hospital0.vercel.app',
        'https://sitaradev-hospitalo.vercel.app',
        // Cloudflare Tunnel (latest)
        'https://evident-parts-action-instructional.trycloudflare.com',
        // Production Render.com
        'https://sitara-frontend.onrender.com',
        'https://sitara-backend.onrender.com',
    ],

    'allowed_origins_patterns' => [
        // Izinkan semua subdomain vercel.app (preview deployments)
        '#^https://.*\.vercel\.app$#',
        // Izinkan semua subdomain trycloudflare.com (tunnel)
        '#^https://.*\.trycloudflare\.com$#',
        // Izinkan semua subdomain onrender.com (Render deployments & previews)
        '#^https://.*\.onrender\.com$#',
        // Izinkan custom domain production jika sudah dikonfigurasi di Render
        // '#^https://.*\.sitaradev\.com$#',
    ],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'Origin',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
    ],

    'exposed_headers' => ['Authorization'],

    'max_age' => 600,

    'supports_credentials' => false,
];
