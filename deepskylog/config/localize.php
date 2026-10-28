<?php

return [

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
