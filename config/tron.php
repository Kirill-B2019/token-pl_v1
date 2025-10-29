<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TRON Network Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for TRON blockchain integration including API endpoints,
    | network settings, and wallet management options.
    |
    */

    'network' => env('TRON_NETWORK', 'mainnet'), // mainnet, testnet, shasta

    'api' => [
        'url' => env('TRON_API_URL', 'https://api.trongrid.io'),
        'key' => env('TRON_API_KEY'),
        'timeout' => env('TRON_API_TIMEOUT', 30),
        'retry_attempts' => env('TRON_RETRY_ATTEMPTS', 3),
    ],

    'wallet' => [
        'auto_create' => env('TRON_AUTO_CREATE_WALLET', true),
        'sync_interval' => env('TRON_SYNC_INTERVAL', 300), // seconds
        'min_trx_balance' => env('TRON_MIN_TRX_BALANCE', 1.0),
        'min_usdt_balance' => env('TRON_MIN_USDT_BALANCE', 0.0),
    ],

    'tokens' => [
        'usdt' => [
            'contract_address' => env('TRON_USDT_CONTRACT', 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'),
            'decimals' => 6,
            'symbol' => 'USDT',
            'name' => 'Tether USD',
        ],
        'trx' => [
            'decimals' => 6,
            'symbol' => 'TRX',
            'name' => 'TRON',
        ],
    ],

    'transactions' => [
        'min_amount_trx' => env('TRON_MIN_AMOUNT_TRX', 0.000001),
        'min_amount_usdt' => env('TRON_MIN_AMOUNT_USDT', 0.000001),
        'max_amount_trx' => env('TRON_MAX_AMOUNT_TRX', 1000000),
        'max_amount_usdt' => env('TRON_MAX_AMOUNT_USDT', 1000000),
        'fee_trx' => env('TRON_FEE_TRX', 0.1), // TRX fee for transactions
        'fee_usdt' => env('TRON_FEE_USDT', 0.0), // USDT fee for transactions
    ],

    'security' => [
        'encrypt_private_keys' => env('TRON_ENCRYPT_PRIVATE_KEYS', true),
        'encrypt_mnemonic' => env('TRON_ENCRYPT_MNEMONIC', true),
        'require_confirmation' => env('TRON_REQUIRE_CONFIRMATION', true),
        'max_daily_transactions' => env('TRON_MAX_DAILY_TRANSACTIONS', 10),
        'max_daily_amount_trx' => env('TRON_MAX_DAILY_AMOUNT_TRX', 1000),
        'max_daily_amount_usdt' => env('TRON_MAX_DAILY_AMOUNT_USDT', 10000),
    ],

    'notifications' => [
        'webhook_url' => env('TRON_WEBHOOK_URL'),
        'webhook_secret' => env('TRON_WEBHOOK_SECRET'),
        'notify_on_transaction' => env('TRON_NOTIFY_ON_TRANSACTION', true),
        'notify_on_balance_change' => env('TRON_NOTIFY_ON_BALANCE_CHANGE', true),
    ],

    'cache' => [
        'balance_cache_ttl' => env('TRON_BALANCE_CACHE_TTL', 60), // seconds
        'price_cache_ttl' => env('TRON_PRICE_CACHE_TTL', 300), // seconds
        'transaction_cache_ttl' => env('TRON_TRANSACTION_CACHE_TTL', 600), // seconds
    ],

    'logging' => [
        'log_transactions' => env('TRON_LOG_TRANSACTIONS', true),
        'log_balance_changes' => env('TRON_LOG_BALANCE_CHANGES', true),
        'log_api_calls' => env('TRON_LOG_API_CALLS', false),
    ],

    'development' => [
        'use_testnet' => env('TRON_USE_TESTNET', false),
        'mock_transactions' => env('TRON_MOCK_TRANSACTIONS', false),
        'debug_mode' => env('TRON_DEBUG_MODE', false),
    ],
];



