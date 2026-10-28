<?php

return [
    /*
     * Determine if the response cache middleware should be enabled.
     */
    'enabled' => env('RESPONSE_CACHE_ENABLED', true),

    /*
     *  The given class will determinate if a request should be cached. The
     *  default class will cache all successful GET-requests.
     *
     *  You can provide your own class given that it implements the
     *  CacheProfile interface.
     */
    // Use a team/user-aware cache profile that only caches pages for guests
    // whose session holds nothing that changes the page, and only with the
    // query parameters listed below.
    'cache_profile' => App\Http\ResponseCache\TeamAwareCacheProfile::class,

    /*
     *  Optionally, you can specify a header that will force a cache bypass.
     *  This can be useful to monitor the performance of your application.
     */
    'cache_bypass_header' => [
        'name' => env('CACHE_BYPASS_HEADER_NAME', null),
        'value' => env('CACHE_BYPASS_HEADER_VALUE', null),
    ],

    /*
     * When using the default CacheRequestFilter this setting controls the
     * default number of seconds responses must be cached.
     */
    // Default cache lifetime: 1 hour. Increase as needed for less DB load.
    'cache_lifetime_in_seconds' => (int) env('RESPONSE_CACHE_LIFETIME', 60 * 60),

    /*
     * This setting determines if a http header named with the cache time
     * should be added to a cached response. This can be handy when
     * debugging.
     */
    'add_cache_time_header' => env('APP_DEBUG', true),

    /*
     * This setting determines the name of the http header that contains
     * the time at which the response was cached
     */
    'cache_time_header_name' => env('RESPONSE_CACHE_HEADER_NAME', 'laravel-responsecache'),

    /*
     * This setting determines if a http header named with the cache age
     * should be added to a cached response. This can be handy when
     * debugging.
     * ONLY works when "add_cache_time_header" is also active!
     */
    'add_cache_age_header' => env('RESPONSE_CACHE_AGE_HEADER', false),

    /*
     * This setting determines the name of the http header that contains
     * the age of cache
     */
    'cache_age_header_name' => env('RESPONSE_CACHE_AGE_HEADER_NAME', 'laravel-responsecache-age'),

    /*
     * Here you may define the cache store that should be used to store
     * requests. This can be the name of any store that is
     * configured in app/config/cache.php. Clearing the response cache
     * flushes this whole store, so it should not be shared with other data.
     */
    'cache_store' => env('RESPONSE_CACHE_DRIVER', 'responsecache'),

    /*
     * Here you may define replacers that dynamically replace content from the response.
     * Each replacer must implement the Replacer interface.
     */
    'replacers' => [
        \Spatie\ResponseCache\Replacers\CsrfTokenReplacer::class,
    ],

    /*
     * If the cache driver you configured supports tags, you may specify a tag name
     * here. All responses will be tagged. When clearing the responsecache only
     * items with that tag will be flushed.
     *
     * You may use a string or an array here.
     */
    'cache_tag' => '',

    /*
     * This class is responsible for generating a hash for a request. This hash
     * is used to look up a cached response.
     */
    'hasher' => \App\Http\ResponseCache\TeamAwareHasher::class,

    /*
     * This class is responsible for serializing responses.
     */
    'serializer' => \Spatie\ResponseCache\Serializers\DefaultSerializer::class,

    /*
     * Query parameters that pages read (App\Http\ResponseCache\TeamAwareHasher
     * keys the cache on these only, sorted). A request with any other
     * parameter is neither served from nor stored in the cache, so a
     * parameter missing from this list only costs caching, never a wrong
     * page. Patterns as in Str::is().
     */
    'query_parameters' => [
        '*page', '*Page', 'adapt_from', 'alternative_names', 'catalog', 'comet', 'comet_name', 'contains',
        'country', 'date', 'dec', 'decl', 'deepsky', 'direction', 'edit_unified_id', 'eyepiece', 'eyepiece_id',
        'filter', 'format', 'images', 'include_subobjects', 'index', 'instrument', 'instrument_id',
        'instrument_set', 'lang', 'language', 'lens', 'lens_id', 'limit', 'list', 'location', 'mag_limit',
        'make', 'max_pages', 'message', 'mode', 'move_to_slug', 'name', 'number', 'obs_type', 'object',
        'object_mag', 'object_names', 'object_refs', 'object_type', 'observation_id', 'observation_type',
        'observations', 'observer', 'orientation', 'paper', 'part_of', 'per_page', 'provider', 'public', 'q',
        'ra', 'radius', 'rasterize', 'receiver', 'reply_to', 'scope', 'search', 'search_query', 'selected',
        'selected_ids', 'session', 'sessions', 'show_drafts', 'sketch_type', 'sketches', 'slug', 'sort',
        'source', 'star_mag', 'subject', 'substring', 'target_slug', 'target_table', 'title', 'to', 'type',
        'unread', 'user',
    ],

    /*
     * Tracking parameters that no page reads: they are left out of the cache
     * key, so links from newsletters or social media share the cached page.
     */
    'ignored_query_parameters' => [
        'utm_*', 'fbclid', 'gclid', 'gbraid', 'wbraid', 'dclid', 'msclkid', 'yclid', 'mc_cid', 'mc_eid',
        'igshid', '_ga', '_gl', 'ref',
    ],
];
