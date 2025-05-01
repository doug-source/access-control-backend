<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['POST, GET, OPTIONS, PATCH, PUT, DELETE'],

    'allowed_origins' => ['http://192.168.1.23:5173', 'http://localhost:5173', 'http://localhost:4173'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type, Authorization, Accept, Origin'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
