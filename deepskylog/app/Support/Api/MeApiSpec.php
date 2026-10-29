<?php

namespace App\Support\Api;

use App\Http\Controllers\Api\MeController;
use App\Models\Observation;
use App\Models\StarPartyParticipant;

/**
 * The OpenAPI 3.1 description of the token API /api/v1/me/... (MeController),
 * served as /api/v1/openapi.json and shown on /api/documentation, so programs
 * besides pydeepskylog (the PiFinder, planetarium and logging programs) can
 * use it. tests/Feature/MeApiSpecTest.php checks that every route of the API
 * is described and that the fields of the responses are those of the
 * schemas: change both when a response changes.
 */
class MeApiSpec
{
    public const VERSION = '1.0.0';

    /**
     * @return array<string, mixed>
     */
    public static function spec(): array
    {
        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'DeepskyLog API',
                'version' => self::VERSION,
                'description' => implode("\n\n", [
                    'Read-only access to the data of the authenticated observer of DeepskyLog: profile, locations, equipment, instrument sets, observations, observing sessions, observing lists and starparties.',
                    'Make a personal access token on '.url('/user/api-tokens').' with the permission "read" and send it as `Authorization: Bearer <token>`. A request without a valid token gets 401, a token without the read permission 403.',
                    'Observations and sessions are paginated with `page` and `per_page` (at most '.MeController::MAX_PER_PAGE.', default '.MeController::DEFAULT_PER_PAGE.') in the shape of Laravel\'s paginator: the rows are in `data`, `next_page_url` is null on the last page.',
                    'Dates of observations are in UT. Texts written in the editor of the site (descriptions, weather, comments) are HTML, cleaned of anything unsafe.',
                    'The Python package pydeepskylog (https://pypi.org/project/pydeepskylog/) wraps this API.',
                ]),
                'contact' => ['name' => 'DeepskyLog', 'url' => url('/')],
            ],
            'servers' => [['url' => url('/')]],
            'security' => [['bearerAuth' => []]],
            'tags' => [
                ['name' => 'Observer', 'description' => 'The profile of the observer'],
                ['name' => 'Equipment', 'description' => 'Locations, instruments, eyepieces, lenses, filters and instrument sets'],
                ['name' => 'Observations', 'description' => 'Observations and observing sessions'],
                ['name' => 'Planning', 'description' => 'Observing lists and starparties'],
            ],
            'paths' => self::paths(),
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'description' => 'A personal access token of '.url('/user/api-tokens').' with the permission "read".',
                    ],
                ],
                'parameters' => [
                    'page' => ['name' => 'page', 'in' => 'query', 'description' => 'The page, from 1.', 'schema' => ['type' => 'integer', 'minimum' => 1, 'default' => 1]],
                    'per_page' => ['name' => 'per_page', 'in' => 'query', 'description' => 'Rows per page.', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => MeController::MAX_PER_PAGE, 'default' => MeController::DEFAULT_PER_PAGE]],
                    'id' => ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                ],
                'responses' => [
                    'Unauthorized' => self::error('No token, or a token that is not valid.'),
                    'Forbidden' => self::error('The token does not have the permission "read".'),
                    'NotFound' => self::error('Not found, or not of the observer.'),
                    'ValidationError' => [
                        'description' => 'A query parameter is not valid.',
                        'content' => ['application/json' => ['schema' => [
                            'type' => 'object',
                            'properties' => [
                                'message' => ['type' => 'string'],
                                'errors' => ['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]],
                            ],
                        ]]],
                    ],
                ],
                'schemas' => self::schemas(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function paths(): array
    {
        $id = ['$ref' => '#/components/parameters/id'];
        $paging = [['$ref' => '#/components/parameters/page'], ['$ref' => '#/components/parameters/per_page']];

        return [
            '/api/v1/me' => ['get' => self::operation('getProfile', 'Observer', 'The profile of the observer', 'Profile')],
            '/api/v1/me/locations' => ['get' => self::operation('listLocations', 'Equipment', 'The locations of the observer, hidden ones included, by name', ['array' => 'Location'])],
            '/api/v1/me/instruments' => ['get' => self::operation('listInstruments', 'Equipment', 'The instruments of the observer, by name', ['array' => 'Instrument'])],
            '/api/v1/me/eyepieces' => ['get' => self::operation('listEyepieces', 'Equipment', 'The eyepieces of the observer, by name', ['array' => 'Eyepiece'])],
            '/api/v1/me/lenses' => ['get' => self::operation('listLenses', 'Equipment', 'The lenses (Barlow lenses, focal reducers) of the observer, by name', ['array' => 'Lens'])],
            '/api/v1/me/filters' => ['get' => self::operation('listFilters', 'Equipment', 'The filters of the observer, by name', ['array' => 'Filter'])],
            '/api/v1/me/instrument-sets' => ['get' => self::operation('listInstrumentSets', 'Equipment', 'The instrument sets of the observer: equipment used together', ['array' => 'InstrumentSet'])],
            '/api/v1/me/observations' => ['get' => self::operation('listObservations', 'Observations', 'The observations of the observer, newest first', ['page' => 'Observation'], [
                ...$paging,
                self::query('from', 'Observed on or after this date (UT).', ['type' => 'string', 'format' => 'date']),
                self::query('to', 'Observed on or before this date (UT).', ['type' => 'string', 'format' => 'date']),
                self::query('type', 'The kind of target.', ['type' => 'string', 'enum' => ['deepsky', 'comet', 'planet', 'lunar', 'solarsystem']]),
                self::query('object', 'The name of the object, as in `object` of the observation ("M 31", not "M31").', ['type' => 'string']),
                self::query('instrument_id', 'Made with this instrument.', ['type' => 'integer']),
                self::query('location_id', 'Made at this location.', ['type' => 'integer']),
                self::query('session_id', 'In this observing session.', ['type' => 'integer']),
                self::query('updated_since', 'Created or changed at or after this moment, to fetch only what changed since the last synchronisation.', ['type' => 'string', 'format' => 'date-time']),
            ], validated: true)],
            '/api/v1/me/observations/{id}' => ['get' => self::operation('getObservation', 'Observations', 'One observation of the observer', 'Observation', [$id], notFound: true)],
            '/api/v1/me/sessions' => ['get' => self::operation('listSessions', 'Observations', 'The observing sessions of the observer, newest first', ['page' => 'Session'], $paging, validated: true)],
            '/api/v1/me/sessions/{id}' => ['get' => self::operation('getSession', 'Observations', 'One observing session of the observer, with the ids of its observations', 'SessionDetail', [$id], notFound: true)],
            '/api/v1/me/observing-lists' => ['get' => self::operation('listObservingLists', 'Planning', 'The observing lists of the observer, by name', ['array' => 'ObservingList'], [
                self::query('include', '`subscribed` also returns the public lists the observer subscribed to.', ['type' => 'string', 'enum' => ['subscribed']]),
            ])],
            '/api/v1/me/observing-lists/{id}' => ['get' => self::operation('getObservingList', 'Planning', 'One observing list with its objects: a list of the observer or a public list', 'ObservingListDetail', [$id], notFound: true)],
            '/api/v1/me/star-parties' => ['get' => self::operation('listStarParties', 'Planning', 'The starparties the observer organises or was invited to, the first night first', ['array' => 'StarParty'])],
        ];
    }

    /**
     * @param  string|array{array?: string, page?: string}  $schema  a schema name, an array of it, or a page of it
     * @param  list<array<string, mixed>>  $parameters
     * @return array<string, mixed>
     */
    private static function operation(string $id, string $tag, string $summary, string|array $schema, array $parameters = [], bool $notFound = false, bool $validated = false): array
    {
        $schema = match (true) {
            is_string($schema) => self::ref($schema),
            isset($schema['array']) => ['type' => 'array', 'items' => self::ref($schema['array'])],
            default => ['allOf' => [self::ref('Page'), ['type' => 'object', 'properties' => ['data' => ['type' => 'array', 'items' => self::ref($schema['page'])]]]]],
        };

        $responses = [
            '200' => ['description' => $summary.'.', 'content' => ['application/json' => ['schema' => $schema]]],
            '401' => ['$ref' => '#/components/responses/Unauthorized'],
            '403' => ['$ref' => '#/components/responses/Forbidden'],
        ];
        if ($notFound) {
            $responses['404'] = ['$ref' => '#/components/responses/NotFound'];
        }
        if ($validated) {
            $responses['422'] = ['$ref' => '#/components/responses/ValidationError'];
        }

        return array_filter([
            'operationId' => $id,
            'tags' => [$tag],
            'summary' => $summary,
            'parameters' => $parameters ?: null,
            'responses' => $responses,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function schemas(): array
    {
        $html = fn (string $description) => self::field('string', $description.' (HTML)', true);

        return [
            'Page' => self::object([
                'current_page' => self::field('integer', 'The page returned.'),
                'data' => ['type' => 'array', 'items' => new \stdClass(), 'description' => 'The rows of the page.'],
                'first_page_url' => self::field('string', 'URL of the first page.', false, 'uri'),
                'from' => self::field('integer', 'Number of the first row of the page, from 1; null on an empty page.', true),
                'last_page' => self::field('integer', 'The last page.'),
                'last_page_url' => self::field('string', 'URL of the last page.', false, 'uri'),
                'links' => ['type' => 'array', 'description' => 'Links to the pages, for a pager.', 'items' => [
                    'type' => 'object',
                    'properties' => [
                        'url' => self::field('string', 'URL of the page; null for a page that does not exist and for the "..." between pages.', true),
                        'label' => self::field('string', 'Its label.'),
                        'page' => self::field('integer', 'The page; null for one that does not exist; missing for the "...".', true),
                        'active' => self::field('boolean', 'Whether it is the page returned.'),
                    ],
                    'required' => ['url', 'label', 'active'],
                ]],
                'next_page_url' => self::field('string', 'URL of the next page; null on the last page.', true, 'uri'),
                'path' => self::field('string', 'URL of the list without the page.', false, 'uri'),
                'per_page' => self::field('integer', 'Rows per page.'),
                'prev_page_url' => self::field('string', 'URL of the previous page; null on the first page.', true, 'uri'),
                'to' => self::field('integer', 'Number of the last row of the page; null on an empty page.', true),
                'total' => self::field('integer', 'Number of rows of all pages.'),
            ]),
            'Profile' => self::object([
                'id' => self::field('integer', 'The id of the observer.'),
                'username' => self::field('string', 'The username, unique.'),
                'name' => self::field('string', 'The full name.'),
                'slug' => self::field('string', 'The part of the URLs of the observer\'s pages, as in '.url('/observers/{slug}').'.'),
                'country' => self::field('string', 'ISO 3166-1 alpha-2 code of the country of residence.', true),
                'language' => self::field('string', 'The language of the site for the observer (en, nl, fr, de, es, sv).', true),
                'observation_language' => self::field('string', 'The language the observer writes observations in.', true),
                'fst_offset' => self::field('number', 'Correction of the observer\'s eyes to the naked-eye limiting magnitude.', true),
                'standard_location_id' => self::field('integer', 'The standard location.', true),
                'standard_instrument_id' => self::field('integer', 'The standard instrument.', true),
                'standard_eyepiece_id' => self::field('integer', 'The standard eyepiece.', true),
                'standard_lens_id' => self::field('integer', 'The lens always used with the standard instrument.', true),
                'standard_instrument_set_id' => self::field('integer', 'The standard instrument set.', true),
                'active_observing_list_id' => self::field('integer', 'The observing list the observer works with.', true),
                'show_inches' => self::field('boolean', 'Whether the observer sees apertures in inches.'),
            ]),
            'Location' => self::object([
                'id' => self::field('integer', 'The id of the location.'),
                'name' => self::field('string', 'The name.'),
                'slug' => self::field('string', 'The part of the URL of its page.'),
                'latitude' => self::field('number', 'Latitude in degrees, north positive.'),
                'longitude' => self::field('number', 'Longitude in degrees, east positive.'),
                'elevation' => self::field('number', 'Elevation in metres.', true),
                'country' => self::field('string', 'The country, in English.', true),
                'timezone' => self::field('string', 'IANA time zone (Europe/Brussels).', true),
                'limiting_magnitude' => self::field('number', 'Typical naked-eye limiting magnitude entered for it.', true),
                'sky_background' => self::field('number', 'Typical sky brightness (SQM, mag/arcsec²) entered for it.', true),
                'sqm' => self::field('number', 'The sky brightness (SQM) used: the one entered, else from the limiting magnitude.', true),
                'nelm' => self::field('number', 'The naked-eye limiting magnitude used: the one entered, else from the SQM.', true),
                'bortle' => self::field('integer', 'The Bortle class of the sky.', true),
                'active' => self::field('boolean', 'Whether the location is in use.'),
                'hidden' => self::field('boolean', 'Whether the location is hidden from other observers.'),
                'standard' => self::field('boolean', 'Whether it is the standard location of the observer.'),
                'description' => $html('The description'),
            ]),
            'Instrument' => self::object([
                'id' => self::field('integer', 'The id of the instrument.'),
                'name' => self::field('string', 'The name.'),
                'slug' => self::field('string', 'The part of the URL of its page.'),
                'aperture_mm' => self::field('number', 'Aperture in mm.'),
                'focal_length_mm' => self::field('number', 'Focal length in mm.', true),
                'fixedMagnification' => self::field('number', 'The magnification of an instrument without eyepieces (binoculars, finder, naked eye: 1).', true),
                'field_of_view_deg' => self::field('number', 'The true field in degrees of an instrument with a fixed magnification, when entered.', true),
                'obstruction_perc' => self::field('number', 'Central obstruction in percent.', true),
                'instrument_type_id' => self::field('integer', 'Its type, in `instrument_type`.', true),
                'instrument_type' => self::lookup('The type (Naked Eye, Binoculars, Refractor, Reflector, ...).'),
                'mount_type_id' => self::field('integer', 'Its mount, in `mount_type`.', true),
                'mount_type' => self::lookup('The mount (Alt/Azimuth, Equatorial, ...).'),
                'make_id' => self::field('integer', 'Its make, in `instrument_make`.', true),
                'instrument_make' => self::lookup('The make.'),
                'flip_image' => self::field('integer', '1 when the image is mirrored left-right.', true),
                'flop_image' => self::field('integer', '1 when the image is mirrored top-bottom.', true),
                'finder_id' => self::field('integer', 'The finder mounted on it (an instrument).', true),
                'lens_id' => self::field('integer', 'The lens always used with it (a coma corrector).', true),
                'active' => self::field('integer', '1 when in use.'),
                'observations' => self::field('integer', 'Number of observations made with it.'),
                'picture' => self::field('string', 'Path of its picture.', true),
                'description' => $html('The description'),
                'user_id' => self::field('integer', 'The id of the observer.'),
                'created_at' => self::field('string', 'Created at.', true, 'date-time'),
                'updated_at' => self::field('string', 'Last changed at.', true, 'date-time'),
            ], strict: false, description: 'An instrument. Fields not listed here are kept for the old DeepskyLog and may go.'),
            'Eyepiece' => self::object([
                'id' => self::field('integer', 'The id of the eyepiece.'),
                'name' => self::field('string', 'The name.'),
                'slug' => self::field('string', 'The part of the URL of its page.'),
                'focal_length_mm' => self::field('number', 'Focal length in mm (the shortest of a zoom eyepiece).'),
                'max_focal_length_mm' => self::field('number', 'The longest focal length of a zoom eyepiece; -1 or null for others.', true),
                'apparentFOV' => self::field('number', 'Apparent field in degrees.', true),
                'field_stop_mm' => self::field('number', 'Field stop in mm; 0 or null when not known.', true),
                'make_id' => self::field('integer', 'Its make, in `eyepiece_make`.', true),
                'eyepiece_make' => self::lookup('The make.'),
                'type_id' => self::field('integer', 'Its type, in `eyepiece_type`.', true),
                'eyepiece_type' => self::lookup('The type (the series of the make).'),
                'active' => self::field('integer', '1 when in use.'),
                'observations' => self::field('integer', 'Number of observations made with it.'),
                'picture' => self::field('string', 'Path of its picture.', true),
                'description' => $html('The description'),
                'user_id' => self::field('integer', 'The id of the observer.'),
                'created_at' => self::field('string', 'Created at.', true, 'date-time'),
                'updated_at' => self::field('string', 'Last changed at.', true, 'date-time'),
            ], strict: false, description: 'An eyepiece. Fields not listed here are kept for the old DeepskyLog and may go.'),
            'Lens' => self::object([
                'id' => self::field('integer', 'The id of the lens.'),
                'name' => self::field('string', 'The name.'),
                'slug' => self::field('string', 'The part of the URL of its page.'),
                'factor' => self::field('number', 'The factor it multiplies the focal length with (2 for a 2× Barlow lens, 0.63 for a focal reducer).'),
                'make_id' => self::field('integer', 'Its make, in `lens_make`.', true),
                'lens_make' => self::lookup('The make.'),
                'active' => self::field('integer', '1 when in use.'),
                'observations' => self::field('integer', 'Number of observations made with it.'),
                'picture' => self::field('string', 'Path of its picture.', true),
                'description' => $html('The description'),
                'user_id' => self::field('integer', 'The id of the observer.'),
                'created_at' => self::field('string', 'Created at.', true, 'date-time'),
                'updated_at' => self::field('string', 'Last changed at.', true, 'date-time'),
            ], strict: false, description: 'A lens. Fields not listed here are kept for the old DeepskyLog and may go.'),
            'Filter' => self::object([
                'id' => self::field('integer', 'The id of the filter.'),
                'name' => self::field('string', 'The name.'),
                'slug' => self::field('string', 'The part of the URL of its page.'),
                'make_id' => self::field('integer', 'Its make, in `filter_make`.', true),
                'filter_make' => self::lookup('The make.'),
                'type_id' => self::field('integer', 'Its type, in `filter_type`.', true),
                'filter_type' => self::lookup('The type (Broad band, Narrow band, O-III, H-beta, Color, ...).'),
                'color_id' => self::field('integer', 'Its color, in `filter_color`, for a color filter.', true),
                'filter_color' => self::lookup('The color.'),
                'wratten' => self::field('string', 'Wratten number of a color filter.', true),
                'schott' => self::field('string', 'Schott number of a color filter.', true),
                'active' => self::field('integer', '1 when in use.'),
                'observations' => self::field('integer', 'Number of observations made with it.'),
                'picture' => self::field('string', 'Path of its picture.', true),
                'description' => $html('The description'),
                'user_id' => self::field('integer', 'The id of the observer.'),
                'created_at' => self::field('string', 'Created at.', true, 'date-time'),
                'updated_at' => self::field('string', 'Last changed at.', true, 'date-time'),
            ], strict: false, description: 'A filter. Fields not listed here are kept for the old DeepskyLog and may go.'),
            'InstrumentSet' => self::object([
                'id' => self::field('integer', 'The id of the set.'),
                'name' => self::field('string', 'The name.'),
                'slug' => self::field('string', 'The part of the URL of its page.'),
                'active' => self::field('boolean', 'Whether the set is in use.'),
                'description' => $html('The description'),
                'instrument_ids' => self::ids('The instruments of the set.'),
                'eyepiece_ids' => self::ids('The eyepieces of the set.'),
                'lens_ids' => self::ids('The lenses of the set.'),
                'filter_ids' => self::ids('The filters of the set.'),
                'location_ids' => self::ids('The locations of the set.'),
            ]),
            'Observation' => self::object([
                'id' => self::field('integer', 'The id of the observation.'),
                'object' => self::field('string', 'The name of what was observed (M 31, C/2023 A3 (Tsuchinshan-ATLAS), Jupiter, Copernicus).'),
                'type' => self::field('string', 'The kind of target: deepsky, comet, planet, lunar (the Moon, a lunar feature or a moon of a planet) or solarsystem (an asteroid).'),
                'target_table' => self::field('string', 'Where the target is kept: objects, cometobjects, planets, moons, lunar_features or asteroids.'),
                'target_key' => self::field('string', 'The key of the target there: the name of a deep-sky object, the id of a comet, the slug of a planet, moon, lunar feature or asteroid.'),
                'date' => self::field('string', 'The date of the observation, in UT.', false, 'date'),
                'time' => self::field('string', 'The time of the observation, in UT (HH:MM:SS); null when not known. Observations of the old DeepskyLog without a time have 23:59:00.', true),
                'instrument_id' => self::field('integer', 'The instrument.', true),
                'location_id' => self::field('integer', 'The location.', true),
                'eyepiece_id' => self::field('integer', 'The eyepiece.', true),
                'filter_id' => self::field('integer', 'The filter.', true),
                'lens_id' => self::field('integer', 'The lens.', true),
                'seeing' => ['type' => ['integer', 'null'], 'enum' => [0, 1, 2, 3, 4, 5, null], 'description' => 'Seeing: 1 excellent, 2 good, 3 moderate, 4 poor, 5 bad; 0 or null not given.'],
                'transparency' => ['type' => ['integer', 'null'], 'enum' => [0, 1, 2, 3, 4, 5, null], 'description' => 'Transparency: 1 prime, 2 good, 3 typical, 4 poor, 5 awful; 0 or null not given.'],
                'limiting_magnitude' => self::field('number', 'The naked-eye limiting magnitude of the night.', true),
                'sqm' => self::field('number', 'The sky brightness (SQM, mag/arcsec²) of the night.', true),
                'class' => ['type' => ['string', 'null'], 'enum' => [...Observation::OBSERVER_CLASSES, null], 'description' => 'The observer\'s own grade: A top observation, B good, C record that the object was seen; null not graded.'],
                'language' => self::field('string', 'The language of the description.', true),
                'description' => $html('The description'),
                'has_drawing' => self::field('boolean', 'Whether the observation has a drawing.'),
                'details' => ['oneOf' => [self::ref('DeepskyDetails'), self::ref('CometDetails'), ['type' => 'null']], 'description' => 'The details of a deep-sky or comet observation; null for others.'],
                'created_at' => self::field('string', 'Created at.', true, 'date-time'),
                'updated_at' => self::field('string', 'Last changed at.', true, 'date-time'),
            ]),
            'DeepskyDetails' => self::object([
                'visibility' => ['type' => ['integer', 'null'], 'description' => 'Visibility: 1 very simple, prominent object; 2 easily seen with direct vision; 3 seen with direct vision; 4 averted vision required; 5 barely seen with averted vision; 6 very questionable; 7 definitely not seen. For a double star: 1 resolved, 2 appears as an 8, 3 not resolved. 0 or null not given.'],
                'small_diameter' => self::field('number', 'The smallest diameter seen, in arcseconds; 0 or null not given.', true),
                'large_diameter' => self::field('number', 'The largest diameter seen, in arcseconds; 0 or null not given.', true),
                'stellar' => self::flag('Stellar.'),
                'extended' => self::flag('Extended.'),
                'resolved' => self::flag('Resolved.'),
                'mottled' => self::flag('Mottled.'),
                'cluster_type' => self::field('string', 'Trumpler-like class of an open cluster (A to J).', true),
                'unusual_shape' => self::flag('Unusual shape.'),
                'partly_unresolved' => self::flag('Partly unresolved.'),
                'color_contrasts' => self::flag('Color contrasts.'),
                'magnification' => self::field('string', 'The magnification used.', true),
                'equal_brightness' => self::flag('A double star whose components are equally bright.'),
                'nice_field' => self::flag('A nice field.'),
                'component1' => self::field('integer', 'Color of the first component of a double star: 1 white, 2 red, 3 orange, 4 yellow, 5 green, 6 blue; -1 or null not given.', true),
                'component2' => self::field('integer', 'Color of the second component.', true),
                'date_dec' => self::field('number', 'Kept for the old DeepskyLog.', true),
            ], description: 'Details of a deep-sky observation.'),
            'CometDetails' => self::object([
                'comet_object_id' => self::field('integer', 'The id of the comet.', true),
                'method' => self::field('string', 'ICQ magnitude method.', true),
                'magnitude' => self::field('number', 'The estimated total magnitude.', true),
                'chart' => self::field('string', 'ICQ reference of the comparison stars.', true),
                'magnification' => self::field('string', 'The magnification used.', true),
                'magnitude_uncertain' => ['type' => 'integer', 'enum' => [0, 1], 'description' => '1 when the magnitude is uncertain.'],
                'less_magnitude' => ['type' => 'integer', 'enum' => [0, 1], 'description' => '1 when the comet was fainter than the magnitude.'],
                'dc' => self::field('string', 'Degree of condensation, 0 (diffuse) to 9 (stellar).', true),
                'coma' => self::field('integer', 'Diameter of the coma in arcminutes.', true),
                'tail' => self::field('integer', 'Length of the tail in arcminutes.', true),
                'position_angle' => self::field('integer', 'Position angle of the tail in degrees.', true),
            ], description: 'Details of a comet observation.'),
            'Session' => self::object(self::sessionProperties()),
            'SessionDetail' => self::object([
                ...self::sessionProperties(),
                'observation_ids' => self::ids('The observations of the session.'),
            ]),
            'ObservingList' => self::object(self::observingListProperties()),
            'ObservingListDetail' => self::object([
                ...self::observingListProperties(),
                'planned_timezone' => self::field('string', 'IANA time zone the times of the list were planned in.', true),
                'items' => ['type' => 'array', 'description' => 'The objects of the list, in its order.', 'items' => self::ref('ObservingListItem')],
            ]),
            'ObservingListItem' => self::object([
                'id' => self::field('integer', 'The id of the item.'),
                'object_name' => self::field('string', 'The name of the object (a deep-sky object, planet, comet, ...).'),
                'description' => self::field('string', 'The note of the list for the object.', true),
                'sort_order' => self::field('integer', 'Its place in the list.', true),
                'planned_at' => self::field('string', 'The moment planned to observe it.', true, 'date-time'),
                'planned_from' => self::field('string', 'The start of the time it is well placed.', true, 'date-time'),
                'planned_until' => self::field('string', 'The end of the time it is well placed.', true, 'date-time'),
                'source_observation_id' => self::field('integer', 'The observation it was added from.', true),
                'added_at' => self::field('string', 'Added at.', true, 'date-time'),
            ]),
            'StarParty' => self::object([
                'id' => self::field('integer', 'The id of the starparty.'),
                'name' => self::field('string', 'The name.'),
                'url' => self::field('string', 'Its page.', false, 'uri'),
                'first_night' => self::field('string', 'The evening of the first night.', false, 'date'),
                'last_night' => self::field('string', 'The evening of the last night.', false, 'date'),
                'visibility' => ['type' => 'string', 'enum' => ['public', 'private'], 'description' => 'Public (listed, anyone may come) or private (invited observers).'],
                'cancelled' => self::field('boolean', 'Whether it was cancelled.'),
                'organiser' => self::field('string', 'The username of the organiser.', true),
                'status' => ['type' => ['string', 'null'], 'enum' => [StarPartyParticipant::INVITED, StarPartyParticipant::GOING, StarPartyParticipant::MAYBE, StarPartyParticipant::DECLINED, null], 'description' => 'The observer\'s answer; null for an organiser who did not answer.'],
                'going_count' => self::field('integer', 'Number of observers who come.'),
                'max_participants' => self::field('integer', 'The most observers who can come; null without a limit.', true),
                'site' => self::object([
                    'name' => self::field('string', 'The name of the site.'),
                    'latitude' => self::field('number', 'Latitude in degrees, north positive.'),
                    'longitude' => self::field('number', 'Longitude in degrees, east positive.'),
                    'elevation' => self::field('number', 'Elevation in metres.', true),
                    'timezone' => self::field('string', 'IANA time zone.', true),
                    'country' => self::field('string', 'The country, in English.', true),
                ], description: 'Where it is held.'),
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function sessionProperties(): array
    {
        return [
            'id' => self::field('integer', 'The id of the session.'),
            'name' => self::field('string', 'The name.'),
            'slug' => self::field('string', 'The part of the URL of its page.', true),
            'begin' => self::field('string', 'Start, as entered by the observer (YYYY-MM-DD HH:MM:SS).', true),
            'end' => self::field('string', 'End, as entered by the observer (YYYY-MM-DD HH:MM:SS).', true),
            'location_id' => self::field('integer', 'The location.', true),
            'active' => self::field('boolean', 'Whether the session is published; false for a draft, which only the observer sees.'),
            'language' => self::field('string', 'The language of the texts.', true),
            'weather' => self::field('string', 'The weather (HTML).', true),
            'equipment' => self::field('string', 'The equipment (HTML).', true),
            'comments' => self::field('string', 'Comments (HTML).', true),
            'other_observers' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'The usernames of the other observers of the session.'],
            'observation_count' => self::field('integer', 'Number of observations in the session.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function observingListProperties(): array
    {
        return [
            'id' => self::field('integer', 'The id of the list.'),
            'name' => self::field('string', 'The name.'),
            'slug' => self::field('string', 'The part of the URL of its page, as in '.url('/observing-list/{slug}').'.'),
            'description' => self::field('string', 'The description.', true),
            'public' => self::field('boolean', 'Whether other observers can see it.'),
            'owner' => self::field('string', 'The username of its owner.', true),
            'own' => self::field('boolean', 'Whether the observer owns it (else subscribed to it).'),
            'active' => self::field('boolean', 'Whether it is the observer\'s active list.'),
            'item_count' => self::field('integer', 'Number of objects.'),
            'created_at' => self::field('string', 'Created at.', true, 'date-time'),
            'updated_at' => self::field('string', 'Last changed at.', true, 'date-time'),
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    private static function object(array $properties, bool $strict = true, ?string $description = null): array
    {
        return array_filter([
            'type' => 'object',
            'description' => $description,
            'properties' => $properties,
            'required' => array_keys($properties),
            'additionalProperties' => $strict ? false : null,
        ], fn ($value) => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private static function field(string $type, string $description, bool $nullable = false, ?string $format = null): array
    {
        return array_filter([
            'type' => $nullable ? [$type, 'null'] : $type,
            'format' => $format,
            'description' => $description,
        ], fn ($value) => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private static function flag(string $description): array
    {
        return ['type' => ['integer', 'null'], 'enum' => [-1, 0, 1, null], 'description' => $description.' 1 yes, 0 no, -1 or null not given.'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function ids(string $description): array
    {
        return ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => $description];
    }

    /**
     * @return array<string, mixed>
     */
    private static function lookup(string $description): array
    {
        return [
            'type' => ['object', 'null'],
            'description' => $description,
            'properties' => ['id' => ['type' => 'integer'], 'name' => ['type' => 'string']],
        ];
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private static function query(string $name, string $description, array $schema): array
    {
        return ['name' => $name, 'in' => 'query', 'description' => $description, 'schema' => $schema];
    }

    /**
     * @return array<string, string>
     */
    private static function ref(string $schema): array
    {
        return ['$ref' => '#/components/schemas/'.$schema];
    }

    /**
     * @return array<string, mixed>
     */
    private static function error(string $description): array
    {
        return [
            'description' => $description,
            'content' => ['application/json' => ['schema' => [
                'type' => 'object',
                'properties' => ['message' => ['type' => 'string']],
            ]]],
        ];
    }
}
