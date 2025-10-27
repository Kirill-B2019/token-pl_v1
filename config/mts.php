<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MTS Bank Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MTS Bank payment gateway integration
    |
    */

    'mts' => [
        'api_url' => env('MTS_API_URL', 'https://api.mtsbank.ru/api/v1'),
        'merchant_id' => env('MTS_MERCHANT_ID'),
        'api_key' => env('MTS_API_KEY'),
        'api_secret' => env('MTS_API_SECRET'),
        'webhook_secret' => env('MTS_WEBHOOK_SECRET'),
        
        'settings' => [
            'timeout' => env('MTS_TIMEOUT', 30),
            'retry_attempts' => env('MTS_RETRY_ATTEMPTS', 3),
            'currency' => env('MTS_CURRENCY', 'RUB'),
            'min_amount' => env('MTS_MIN_AMOUNT', 1),
            'max_amount' => env('MTS_MAX_AMOUNT', 1000000),
        ],

        'urls' => [
            'success' => env('MTS_SUCCESS_URL', 'https://cardfly.online/client/payment/success'),
            'fail' => env('MTS_FAIL_URL', 'https://cardfly.online/client/payment/fail'),
            'webhook' => env('MTS_WEBHOOK_URL', 'https://cardfly.online/api/mts/webhook'),
            'notification' => env('MTS_NOTIFICATION_URL', 'https://cardfly.online/api/mts/notification'),
        ],

        'commission' => [
            'rate' => env('MTS_COMMISSION_RATE', 0.022), // 2.2%
            'min_commission' => env('MTS_MIN_COMMISSION', 10), // 10 RUB
            'max_commission' => env('MTS_MAX_COMMISSION', 1000), // 1000 RUB
        ],

        'security' => [
            'verify_signature' => env('MTS_VERIFY_SIGNATURE', true),
            'allowed_ips' => env('MTS_ALLOWED_IPS', ''),
            'log_requests' => env('MTS_LOG_REQUESTS', true),
        ],
    ],
];
