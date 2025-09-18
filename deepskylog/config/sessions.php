<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Session feature flags
    |--------------------------------------------------------------------------
    |
    | allow_admin_override: when true, users with administrator privileges may
    | adapt or delete sessions they do not own. Default: false.
    |
    */
    'allow_admin_override' => env('SESSIONS_ALLOW_ADMIN_OVERRIDE', false),
];
