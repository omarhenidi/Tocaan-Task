<?php

return [
    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'credit_card'),
    'gateways' => [
        'credit_card' => [
            'enabled' => env('PAYMENT_CREDIT_CARD_ENABLED', true),
            'driver' => \App\Services\Client\Payment\Gateways\CreditCardGateway::class,
            'api_key' => env('STRIPE_API_KEY'),
            'secret' => env('STRIPE_SECRET'),
        ],
        'paypal' => [
            'enabled' => env('PAYMENT_PAYPAL_ENABLED', true),
            'driver' => \App\Services\Client\Payment\Gateways\PayPalGateway::class,
            'client_id' => env('PAYMENT_PAYPAL_CLIENT_ID'),
            'secret' => env('PAYMENT_PAYPAL_SECRET'),
        ],
    ],

];
