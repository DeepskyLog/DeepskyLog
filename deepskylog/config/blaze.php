<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Blaze
    |--------------------------------------------------------------------------
    |
    | Blaze, installed with PowerGrid 7, compiles every Blade view of the
    | site with its own parser, which broke views such as the navigation menu
    | (a component attribute with quotes inside its quotes). PowerGrid only
    | uses it to speed up its icons, which work without it.
    |
    */

    'enabled' => env('BLAZE_ENABLED', false),

    'debug' => env('BLAZE_DEBUG', false),

];
