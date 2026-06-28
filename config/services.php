<?php

return [
    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openweather' => [
        'key' => env('OPENWEATHER_API_KEY'),
        'latitude' => env('OPENWEATHER_LATITUDE', -6.5728619),
        'longitude' => env('OPENWEATHER_LONGITUDE', 106.7647475),
        'url' => env('OPENWEATHER_API_URL', 'https://api.openweathermap.org/data/2.5/weather'),
        'timeout' => env('OPENWEATHER_TIMEOUT', 5),
    ],

    'lstm' => [
        'predict_url' => env('LSTM_PREDICT_URL', 'http://127.0.0.1:5000/predict'),
        'generate_url' => env('LSTM_GENERATE_URL', 'http://127.0.0.1:5000/generate-prediksi'),
    ],
];
