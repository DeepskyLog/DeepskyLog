<?php

return [

    /**
     * Localize types of translation strings.
     */
    'localize' => [
        /**
         * Short keys. This is the default for Laravel.
         * They are stored in PHP files inside folders name by their locale code.
         * Laravel comes with default: auth.php, pagination.php, passwords.php and validation.php
         *
         * Disabled on purpose. The short-key files in resources/lang/<locale>/ are
         * maintained by hand. The localizator rewrites them with var_export(), which
         * drops the comments and blank-line grouping, and its parser cannot see through
         * interpolated keys such as __("observations.visibility.$visKey"): it stores the
         * literal '$visKey' as a translation key. Keeping this off means
         * `php artisan localize <lang>` only touches the <locale>.json files.
         */
        'default' => false,

        /**
         * Translations strings as key.
         * They are stored in JSON file for each locale.
         */
        'json' => true,
    ],

    /**
     * Search criteria for files.
     */
    'search' => [
        /**
         * Directories which should be looked inside.
         */
        'dirs' => ['resources/views'],

        /**
         * Subdirectories which will be excluded.
         * The values must be relative to the included directory paths.
         */
        'exclude' => [
            //
        ],

        /**
         * Patterns by which files should be queried.
         * The values can be a regular expression, glob, or just a string.
         */
        'patterns' => ['*.php'],

        /**
         * Functions that the strings will be extracted from.
         * Add here any custom defined functions.
         * NOTE: The translation string should always be the first argument.
         */
        'functions' => ['__', 'trans', '@lang'],
    ],

    /**
     * Should the localize command sort extracted strings alphabetically?
     */
    'sort' => true,

];
