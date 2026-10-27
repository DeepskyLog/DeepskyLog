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
        // Support both GITHUB_CLIENT_ID / GITHUB_CLIENT_SECRET and legacy GITHUB_KEY / GITHUB_SECRET
        'client_id' => env('GITHUB_CLIENT_ID', env('GITHUB_KEY')),
        'client_secret' => env('GITHUB_CLIENT_SECRET', env('GITHUB_SECRET')),
        // Support both GITHUB_REDIRECT and GITHUB_REDIRECT_URI
        'redirect' => env('GITHUB_REDIRECT', env('GITHUB_REDIRECT_URI')),
    ],

    'facebook' => [
        // Support both FACEBOOK_CLIENT_ID / FACEBOOK_CLIENT_SECRET and legacy FACEBOOK_KEY / FACEBOOK_SECRET
        'client_id' => env('FACEBOOK_CLIENT_ID', env('FACEBOOK_KEY')),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET', env('FACEBOOK_SECRET')),
        // Support both FACEBOOK_REDIRECT and FACEBOOK_REDIRECT_URI
        'redirect' => env('FACEBOOK_REDIRECT', env('FACEBOOK_REDIRECT_URI')),
    ],

    'twitter' => [
        // Support both TWITTER_CLIENT_ID / TWITTER_CLIENT_SECRET and legacy TWITTER_KEY / TWITTER_SECRET
        'client_id' => env('TWITTER_CLIENT_ID', env('TWITTER_KEY')),
        'client_secret' => env('TWITTER_CLIENT_SECRET', env('TWITTER_SECRET')),
        // Support both TWITTER_REDIRECT and TWITTER_REDIRECT_URI
        'redirect' => env('TWITTER_REDIRECT', env('TWITTER_REDIRECT_URI')),
    ],

    'bigdatacloud' => [
        // Reverse geocoding and time zones of new locations
        'key' => env('BIGDATA_API_KEY'),
    ],

    'lightpollutionmap' => [
        // Sky brightness of new locations
        'key' => env('LIGHTPOLLUTIONMAP_API_KEY'),
    ],

    'open_meteo' => [
        // The cloud forecast of the coming nights (App\Services\Weather\NightForecast), free without a key
        'url' => env('OPEN_METEO_URL', 'https://api.open-meteo.com/v1/forecast'),
        'enabled' => (bool) env('WEATHER_FORECAST', true),
    ],

    'aavso' => [
        // The variable star index and latest estimates of the AAVSO (App\Services\AavsoVariableStars), free without a key
        'url' => env('AAVSO_VSX_URL', 'https://vsx.aavso.org/index.php'),
        'enabled' => (bool) env('AAVSO', true),
        // Milliseconds between two requests of aavso:refresh (more requests trip the AAVSO's firewall)
        'delay' => (int) env('AAVSO_DELAY_MS', 1000),
    ],

    'astroforum' => [
        // The threads of the objects of the month of Astroforum (App\Services\ObjectsOfTheMonth\AstroforumFeed), read with their permission
        'feed' => env('ASTROFORUM_FEED_URL', 'https://www.astroforum.nl/forums/objecten-van-de-maand.49/index.rss'),
        'enabled' => (bool) env('ASTROFORUM', true),
        // Milliseconds between two requests to the forum
        'delay' => (int) env('ASTROFORUM_DELAY_MS', 1000),
    ],

    'google' => [
        // GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET are already standard
        'client_id' => env('GOOGLE_CLIENT_ID', env('GOOGLE_KEY')),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', env('GOOGLE_SECRET')),
        // Support both GOOGLE_REDIRECT and GOOGLE_CALLBACK_URL
        'redirect' => env('GOOGLE_REDIRECT', env('GOOGLE_CALLBACK_URL')),
    ],

];
