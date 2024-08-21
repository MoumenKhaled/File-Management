<?php

return [
    'includePaths' => [
        base_path('app')
    ],
    'excludePaths' => [
        base_path('vendor')
    ],
    'cacheDir' => storage_path('app/aop'),
    'aspects' => [
        'logging' => [
            'class' => \App\Aspects\LogRequestsAndResponses::class,
            'pointcuts' => [
                'App\\Http\\Controllers\\Auth\\AuthController::*',
            ],
        ],
    ],
];