<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.eu.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'github' => [
        'client_id' => env('{PROVIDER}_CLIENT_ID'),
        'client_secret' => env('{PROVIDER}_CLIENT_SECRET'),
        'redirect' => env('{PROVIDER}_REDIRECT'),
    ],

    'facebook' => [
        'client_id' => env('{PROVIDER}_CLIENT_ID'),
        'client_secret' => env('{PROVIDER}_CLIENT_SECRET'),
        'redirect' => env('{PROVIDER}_REDIRECT'),
    ],

    'twitter' => [
        'client_id' => env('{PROVIDER}_CLIENT_ID'),
        'client_secret' => env('{PROVIDER}_CLIENT_SECRET'),
        'redirect' => env('{PROVIDER}_REDIRECT'),
    ],

    'google' => [
        'client_id' => env('{PROVIDER}_CLIENT_ID'),
        'client_secret' => env('{PROVIDER}_CLIENT_SECRET'),
        'redirect' => env('{PROVIDER}_REDIRECT'),
    ],

];
