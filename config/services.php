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

    'meta' => [
        'graph_version' => env('META_GRAPH_API_VERSION', 'v21.0'),

        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'embedded_signup_config_id' => env('META_EMBEDDED_SIGNUP_CONFIG_ID'),

        'dominios_midia_permitidos' => array_filter(array_map('trim', explode(
            ',',
            env('META_MEDIA_ALLOWED_HOSTS', 'lookaside.fbsbx.com')
        ))),
    ],

    'n8n' => [

        'master_token' => env('N8N_MASTER_TOKEN'),

        'webhook_base_url' => env('N8N_WEBHOOK_BASE_URL', 'https://n8n.example.com'),
    ],

];
