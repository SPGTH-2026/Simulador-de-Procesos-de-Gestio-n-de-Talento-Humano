<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | El frontend vive en otro origen (localhost:5173 en desarrollo), así que
    | necesita credenciales para que la cookie de sesión viaje. Por eso el
    | origen no puede ser "*": tiene que ser explícito.
    |
    | CORS_ALLOWED_ORIGINS acepta varios orígenes separados por coma. Si no se
    | define, se usa FRONTEND_URL, de forma que el mismo valor controla a dónde
    | redirige el callback de Google y qué origen se permite aquí.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', env(
        'CORS_ALLOWED_ORIGINS',
        env('FRONTEND_URL', 'http://localhost:5173'),
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
