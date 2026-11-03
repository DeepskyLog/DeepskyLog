<?php

namespace App\Services\FieldViews;

use App\Models\DeepskyObject;
use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\InstrumentSet;
use App\Models\Lens;
use App\Models\Location;
use App\Models\User;
use App\Services\AtmosphericExtinction;
use App\Services\ContrastReserveCalculator;
use App\Services\DoubleStarResolvability;
use App\Services\FilterAdvice;
use App\Services\LocalHorizon;
use App\Services\TelescopicLimitingMagnitude;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Magnitude;
use deepskylog\AstronomyLibrary\Time;
use Illuminate\Support\Collection;

/**
 * The three views of an object: with the naked eye, in the finder of the
 * telescope and in the telescope itself, for an observer's equipment,
 * location and moment. Binoculars take the place of the telescope, without
 * a finder unless one is chosen: their image is upright and not mirrored,
 * and both eyes see fainter (TelescopicLimitingMagnitude::BINOCULAR_GAIN,
 * ContrastReserveCalculator::BINOCULAR_GAIN).
 *
 * Each view has the field of view of its instrument and the limiting
 * magnitude it reaches under the sky of the location (its NELM, or the one
 * that follows from its SQM), dimmed by the air mass at the altitude of the
 * object. The naked-eye view and the instruments on an alt-azimuth mount
 * have the zenith up; an equatorial mount keeps north up. The finder and
 * the telescope mirror the image as their settings say.
 */
class FieldViewBuilder
{
    /** Half the field of the naked-eye view. */
    public const NAKED_EYE_RADIUS_DEG = 45.0;

    /** Limiting magnitude when the location has neither a NELM nor an SQM. */
    public const DEFAULT_NELM = 6.0;

    /** Deep-sky objects are drawn to this many magnitudes above the star limit. */
    private const OBJECT_LIMIT_OFFSET = 1.0;

    /** Instrument types with mirrors, which transmit less light. */
    public function __construct(private FieldViewRenderer $renderer)
    {
    }

    /** Step with which the night is searched for the highest point of the object. */
    private const NIGHT_STEP_MINUTES = 5;

    /**
     * The lens is 'none' for no lens; without a choice it is the lens of the telescope.
     *
     * @param  array{set?: mixed, instrument?: mixed, eyepiece?: mixed, lens?: mixed, finder?: mixed, location?: mixed, datetime?: mixed, night?: mixed}  $options
     *     ids of the observer's instrument set ('all' for all equipment), equipment and location;
     *     the local date and time ('Y-m-d\TH:i'), or else the night ('Y-m-d' of its evening) in
     *     which the object is shown at its highest. What is missing or not the observer's is taken
     *     from the defaults: a set with the standard telescope, that telescope, the standard location, and the night
     *     of the ephemeris date.
     */
    public function build(User $user, DeepskyObject $object, array $options = [], string $theme = 'dark', int $size = 600): array
    {
        $set      = $this->instrumentSet($user, $options['set'] ?? null);
        $choices  = $this->choices($user, $set);
        $warnings = [];

        $location   = $this->pick($choices['locations'], $options['location'] ?? null, $user->stdlocation);
        $instrument = $this->pick($choices['instruments'], $options['instrument'] ?? null, $user->stdtelescope);
        // The lens: the one chosen ('none' for none), else the one always used with the telescope.
        $lensOption = $options['lens'] ?? null;
        $lens       = $lensOption === 'none' ? null : $this->pick($choices['lenses'], $lensOption, $instrument?->lens_id, false);

        $finderOption = $options['finder'] ?? null;
        if ($finderOption === 'none') {
            $finder = null;
        } else {
            $finder = $this->pick($choices['finders'], $finderOption, $instrument?->finder_id, false);
        }

        $timezone = $location && $location->timezone ? $location->timezone : config('app.timezone', 'UTC');
        $hasPlace = $location && abs((float) $location->latitude) <= 90 && abs((float) $location->longitude) <= 180;

        // The moment: the one chosen, else the highest point of the object
        // in the night, else now.
        $night    = $this->night($options['night'] ?? null, $timezone);
        $moment   = $this->chosenMoment($options['datetime'] ?? null, $timezone);
        $timeMode = 'chosen';
        $darkness = null;
        if ($moment === null) {
            $highest = $hasPlace ? $this->highestAtNight($object, $location, $night, $timezone) : null;
            if ($highest !== null) {
                [$moment, $darkness] = $highest;
                $timeMode            = 'highest';
            } else {
                $moment   = Carbon::now($timezone)->startOfMinute();
                $timeMode = 'now';
                if ($hasPlace) {
                    $warnings[] = __(':object is not above the horizon during the darkness of the night of :night; the current time is used.', ['object' => $object->name, 'night' => $night]);
                }
            }
        } else {
            $night = $moment->copy()->subHours(12)->toDateString();
        }

        // The sky
        $nelm = $location ? $location->getNelm((float) ($user->fstOffset ?? 0)) : null;
        if ($nelm === null || $nelm <= 0) {
            $nelm       = self::DEFAULT_NELM;
            $warnings[] = $location
                ? __('The location has no limiting magnitude or SQM; a limiting magnitude of :nelm is used.', ['nelm' => self::DEFAULT_NELM])
                : __('You have no location; a limiting magnitude of :nelm is used, with north up.', ['nelm' => self::DEFAULT_NELM]);
        }
        $nelm = max(2.0, min(8.0, (float) $nelm));

        $raDeg  = (float) $object->ra * 15.0;
        $decDeg = (float) $object->decl;

        $lst          = null;
        $latitude     = null;
        $altitude     = null;
        $azimuth      = null;
        $localHorizon = $hasPlace ? LocalHorizon::forLocation($location) : null;
        if ($hasPlace) {
            $geo      = new GeographicalCoordinates((float) $location->longitude, (float) $location->latitude);
            $sidereal = Time::meanSiderialTime($moment->copy()->utc(), $geo);
            $lst      = ($sidereal->hour + $sidereal->minute / 60 + ($sidereal->second + $sidereal->microsecond / 1e6) / 3600) * 15.0;
            $latitude = (float) $location->latitude;

            $horizontal = (new EquatorialCoordinates((float) $object->ra, $decDeg))->convertToHorizontal($geo, $sidereal);
            $altitude   = $horizontal->getAltitude()->getCoordinate();
            // The library counts the azimuth from the south.
            $azimuth = fmod($horizontal->getAzimuth()->getCoordinate() + 180.0 + 360.0, 360.0);

            if ($altitude < 0) {
                $warnings[] = __(':object is below the horizon at this moment.', ['object' => $object->name]);
            } elseif ($localHorizon && $altitude < $localHorizon->altitudeAt($azimuth)) {
                $warnings[] = __(':object is behind the local horizon of the location at this moment.', ['object' => $object->name]);
            }
        }

        // Dimming by the atmosphere at the altitude of the object.
        $extinction = $altitude !== null && $altitude > 0 ? AtmosphericExtinction::magnitudes($altitude) : 0.0;

        // The contrast reserve: the SQM of the location as on the object
        // page, else that of the limiting magnitude used; the object dimmed
        // by the extinction, and not seen below the horizon.
        $sqm      = $location?->getSqm();
        $sqm      = is_numeric($sqm) && (float) $sqm > 0 ? (float) $sqm : Magnitude::nelmToSqm($nelm);
        $visible  = $altitude === null || $altitude  > 0;
        $contrast = fn (Instrument $scope, mixed $magnification) => $visible
            ? $this->contrastReserve($object, $sqm, $extinction, $scope, $magnification)
            : ['value' => null, 'category' => 'not_visible'];

        // Whether a double star is split at the magnification of a view
        $doubleStar = function (Instrument $scope, mixed $magnification, float $limit) use ($object, $visible) {
            $result = $visible ? DoubleStarResolvability::atMagnification($object, (float) $scope->aperture_mm, $magnification, $limit) : null;

            return $result === null ? null : DoubleStarResolvability::present($result);
        };

        $target = [
            'name'  => $object->name,
            'ra'    => $raDeg,
            'dec'   => $decDeg,
            'type'  => $object->type,
            'diam1' => (float) $object->diam1,
            'diam2' => (float) $object->diam2,
            'pa'    => (float) $object->pa,
        ];
        $base = [
            'ra'       => $raDeg,
            'dec'      => $decDeg,
            'target'   => $target,
            'lst'      => $lst,
            'latitude' => $latitude,
            'theme'    => $theme,
            'size'     => $size,
        ];

        // The telescope
        $eyepiece      = null;
        $telescopeFov  = null;
        $magnification = null;
        if ($instrument) {
            if ((float) $instrument->fixedMagnification > 0) {
                $telescopeFov = $instrument->fixedFieldOfViewDeg();
            } else {
                $eyepiece = $this->pick($choices['eyepieces'], $options['eyepiece'] ?? null, null, false)
                    ?? $this->defaultEyepiece($object, $instrument, $lens, $location, $choices['eyepieces']);
                $telescopeFov = $eyepiece ? $instrument->eyepieceFieldOfViewDeg($eyepiece, $lens) : null;
            }
            $magnification = $instrument->magnificationFor($eyepiece, $lens);
        }

        $finderFov = $finder?->fixedFieldOfViewDeg();
        $up        = $instrument && $this->isEquatorial($instrument) ? 'north' : 'zenith';
        $binocular = (bool) $instrument?->isBinocular();
        $title     = $binocular ? __('Binoculars') : __('Telescope');

        $finderLimit = $finder && $finderFov
            ? round(TelescopicLimitingMagnitude::forInstrument($nelm, $finder, (float) $finder->fixedMagnification) - $extinction, 1)
            : null;

        // The star-hop route: through the finder, else through the binoculars.
        $hopScope = $finderLimit !== null ? $finder : ($binocular && $telescopeFov ? $instrument : null);
        $hopField = $finderLimit !== null ? $finderFov : $telescopeFov;
        $hopLimit = $finderLimit ?? ($hopScope ? TelescopicLimitingMagnitude::forInstrument($nelm, $hopScope, $magnification ? (float) $magnification : null) - $extinction : null);
        $route    = $hopScope ? StarHopRoute::find($raDeg, $decDeg, $hopField / 2, $hopLimit, $nelm, (float) $object->diam1 / 7200.0) : null;

        $views = [];

        // With the naked eye
        $nakedEye = $base + [
            'radius_deg'     => self::NAKED_EYE_RADIUS_DEG,
            'star_limit'     => $nelm,
            'up'             => 'zenith',
            'horizon'        => true,
            'local_horizon'  => $localHorizon?->altitudes,
            'extinction'     => true,
            'constellations' => true,
            'star_names'     => true,
            'route'          => $route ? ['points' => array_map(fn (array $step) => ['ra' => $step['ra'], 'dec' => $step['dec'], 'label' => $step['kind'] === 'start' ? $step['label'] : null], $route)] : null,
            'overlays'       => array_values(array_filter([
                $finderFov ? ['radius_deg' => $finderFov / 2, 'label' => __('Finder')] : null,
                $binocular && $telescopeFov ? ['radius_deg' => $telescopeFov / 2, 'label' => __('Binoculars')] : null,
            ])),
        ];
        $views['naked_eye'] = [
            'title'    => __('Naked eye'),
            'subtitle' => $altitude !== null
                ? __('Altitude :alt°, azimuth :az° (:direction)', ['alt' => round($altitude), 'az' => round($azimuth), 'direction' => $this->direction($azimuth)])
                : null,
            'fov'   => self::formatAngle(2 * self::NAKED_EYE_RADIUS_DEG),
            'limit' => round($nelm, 1),
            'svg'   => $this->renderer->render($nakedEye),
        ];

        // In the finder
        if ($finderLimit !== null) {
            $views['finder'] = [
                'title'    => __('Finder'),
                'subtitle' => html_entity_decode($finder->fullName()).($finder->fixedMagnification ? ' · '.(int) $finder->fixedMagnification.'x' : ''),
                'fov'      => self::formatAngle($finderFov).($finder->hasEstimatedFieldOfView() ? ' ('.__('estimated').')' : ''),
                'limit'    => $finderLimit,
                'contrast' => $contrast($finder, $finder->fixedMagnification),
                'double'   => $doubleStar($finder, $finder->fixedMagnification, $finderLimit),
                'svg'      => $this->renderer->render($base + [
                    'radius_deg'    => $finderFov / 2,
                    'star_limit'    => $finderLimit,
                    'object_limit'  => $finderLimit - self::OBJECT_LIMIT_OFFSET,
                    'up'            => $up,
                    'flip'          => $this->flips($finder, 'flip_image'),
                    'flop'          => $this->flips($finder, 'flop_image'),
                    'object_labels' => true,
                    'overlays'      => $telescopeFov ? [['radius_deg' => $telescopeFov / 2, 'label' => '']] : [],
                    'route'         => $route ? ['points' => $this->routePoints($route)] : null,
                ]),
            ];
        } elseif (! $binocular) {
            $views['finder'] = [
                'title'   => __('Finder'),
                'message' => $instrument
                    ? __('No finderscope is coupled to :instrument. Choose one above, or set it on the instrument page.', ['instrument' => html_entity_decode($instrument->fullName())])
                    : __('Choose a finderscope above.'),
            ];
        }

        // In the telescope
        if ($instrument && $telescopeFov) {
            $telescopeLimit = round(TelescopicLimitingMagnitude::forInstrument($nelm, $instrument, $magnification ? (float) $magnification : null) - $extinction, 1);
            $subtitle       = html_entity_decode($instrument->fullName());
            if ($eyepiece) {
                $subtitle .= ' · '.html_entity_decode($eyepiece->fullName());
            }
            if ($lens) {
                $subtitle .= ' · '.html_entity_decode($lens->name);
            }
            if ($magnification) {
                $subtitle .= ' · '.$magnification.'x';
            }

            $views['telescope'] = [
                'title'    => $title,
                'subtitle' => $subtitle,
                'fov'      => self::formatAngle($telescopeFov).($eyepiece === null && $instrument->hasEstimatedFieldOfView() ? ' ('.__('estimated').')' : ''),
                'limit'    => $telescopeLimit,
                'contrast' => $contrast($instrument, $magnification),
                'double'   => $doubleStar($instrument, $magnification, $telescopeLimit),
                'filters'  => $visible ? $this->filterAdvice($user, $set, $object, $instrument, $lens, $choices['eyepieces'], $sqm, $extinction, $magnification) : [],
                'svg'      => $this->renderer->render($base + [
                    'radius_deg'         => $telescopeFov / 2,
                    'star_limit'         => $telescopeLimit,
                    'object_limit'       => $telescopeLimit - self::OBJECT_LIMIT_OFFSET,
                    'unknown_magnitudes' => true,
                    'up'                 => $up,
                    'flip'               => $this->flips($instrument, 'flip_image'),
                    'flop'               => $this->flips($instrument, 'flop_image'),
                    'object_labels'      => true,
                ]),
            ];
        } else {
            $views['telescope'] = [
                'title'   => $title,
                'message' => ! $instrument
                    ? __('Add an instrument to see the view through the telescope.')
                    : __('The field of view is unknown: choose an eyepiece with a focal length and an apparent field of view or field stop.'),
            ];
        }

        $starHop = $hopScope ? $this->starHop($route, $base, $hopScope, $hopField, $hopLimit, $up) : null;

        return [
            'object'      => $object,
            'location'    => $location,
            'instrument'  => $instrument,
            'eyepiece'    => $eyepiece,
            'lens'        => $lens,
            'finder'      => $finder,
            'moment'      => $moment,
            'timezone'    => $timezone,
            'time_mode'   => $timeMode,
            'night'       => $night,
            'darkness'    => $darkness,
            'set'         => $set,
            'nelm'        => round($nelm, 1),
            'altitude'    => $altitude,
            'azimuth'     => $azimuth,
            'orientation' => $up,
            'choices'     => $choices,
            'views'       => $views,
            'star_hop'    => $starHop,
            'warnings'    => $warnings,
        ];
    }

    /**
     * The star-hop route as a chart of its own, turned and mirrored as the
     * finder (or binoculars) shows it, with the field around every star, and
     * the steps, each with the hour on a clock face towards which the next
     * star lies in that view; a message when there is no route.
     *
     * @param  list<array>|null  $route  StarHopRoute::find()
     * @return array{title: string, subtitle?: string, fov?: string, limit?: float, field?: string, steps?: list<array>, svg?: string, message?: string}
     */
    private function starHop(?array $route, array $base, Instrument $scope, float $field, float $limit, string $up): array
    {
        $title = __('Star hop');
        if ($route === null) {
            return [
                'title'   => $title,
                'message' => __('There is no star-hop route to :object: no chain of stars within :distance° leads to it in steps that fit in the field of :instrument.', [
                    'object'     => $base['target']['name'],
                    'distance'   => (int) StarHopRoute::MAX_RADIUS_DEG,
                    'instrument' => html_entity_decode($scope->fullName()),
                ]),
            ];
        }

        // The centre of the route and the radius that holds it with a field around each star.
        $sum = [0.0, 0.0, 0.0];
        foreach ($route as $step) {
            $sum[0] += cos(deg2rad($step['dec'])) * cos(deg2rad($step['ra']));
            $sum[1] += cos(deg2rad($step['dec'])) * sin(deg2rad($step['ra']));
            $sum[2] += sin(deg2rad($step['dec']));
        }
        $ra     = fmod(rad2deg(atan2($sum[1], $sum[0])) + 360.0, 360.0);
        $dec    = rad2deg(atan2($sum[2], hypot($sum[0], $sum[1])));
        $extent = 0.0;
        foreach ($route as $step) {
            $extent = max($extent, rad2deg(acos(max(-1.0, min(1.0, sin(deg2rad($dec)) * sin(deg2rad($step['dec'])) + cos(deg2rad($dec)) * cos(deg2rad($step['dec'])) * cos(deg2rad($step['ra'] - $ra)))))));
        }
        $radius    = max($extent + $field / 2 * 1.1, $field * 0.75);
        $starLimit = round(min(StarHopRoute::MAX_HOP_MAGNITUDE, $limit - StarHopRoute::HOP_MARGIN) + 0.5, 1);

        $svg = $this->renderer->render(array_merge($base, [
            'ra'             => $ra,
            'dec'            => $dec,
            'radius_deg'     => $radius,
            'star_limit'     => $starLimit,
            'up'             => $up,
            'flip'           => $this->flips($scope, 'flip_image'),
            'flop'           => $this->flips($scope, 'flop_image'),
            'constellations' => true,
            'route'          => ['points' => $this->routePoints($route), 'field_deg' => $field / 2],
        ]));

        // The direction of each hop in the chart just drawn, as an hour on a clock face.
        $steps = [];
        foreach ($route as $n => $step) {
            $clock = null;
            if ($n > 0) {
                $from = $this->renderer->project($route[$n - 1]['ra'], $route[$n - 1]['dec']);
                $to   = $this->renderer->project($step['ra'], $step['dec']);
                if ($from && $to) {
                    $hour  = (int) round(fmod(rad2deg(atan2($to[0] - $from[0], $from[1] - $to[1])) + 360.0, 360.0) / 30.0) % 12;
                    $clock = $hour === 0 ? 12 : $hour;
                }
            }
            $steps[] = $step + ['number' => $n + 1, 'clock' => $clock];
        }

        return [
            'title'    => $title,
            'subtitle' => html_entity_decode($scope->fullName()),
            'fov'      => self::formatAngle(2 * $radius),
            'limit'    => $starLimit,
            'field'    => self::formatAngle($field),
            'steps'    => $steps,
            'svg'      => $svg,
        ];
    }

    /**
     * The points of a route for the renderer, the stars numbered from 1 and
     * the start star with its name.
     *
     * @param  list<array>  $route
     * @return list<array{ra: float, dec: float, label: ?string}>
     */
    private function routePoints(array $route): array
    {
        $points = [];
        foreach ($route as $n => $step) {
            $label = null;
            if ($step['kind'] !== 'target') {
                $label = ($n + 1).($step['label'] !== null ? ' '.$step['label'] : '');
            }
            $points[] = ['ra' => $step['ra'], 'dec' => $step['dec'], 'label' => $label];
        }

        return $points;
    }

    /**
     * The contrast reserve of the object at the magnification, with its
     * category; null when the object has no surface brightness (no
     * magnitude or size) or the magnification is unknown.
     *
     * @return array{value: ?float, category: string}|null
     */
    private function contrastReserve(DeepskyObject $object, float $sqm, float $extinction, Instrument $scope, mixed $magnification): ?array
    {
        $aperture = (float) $scope->aperture_mm;
        if (! ContrastReserveCalculator::appliesTo($object->type) || $aperture <= 0 || ! is_numeric($magnification) || (float) $magnification <= 0
                           || ! is_numeric($object->mag) || (float) $object->mag >= 99 || (float) $object->mag == 0.0) {
            return null;
        }

        try {
            $target = ContrastReserveCalculator::target($object->diam1, $object->diam2, (float) $object->mag + $extinction);
            $sbobj  = $target->calculateSBObj();
            $value  = $sbobj === null ? null : ContrastReserveCalculator::contrastAt($target, $sbobj, $sqm, $aperture, (float) $magnification, $scope->isBinocular());
        } catch (\Throwable) {
            return null;
        }

        return is_numeric($value)
            ? ['value' => round((float) $value, 2) + 0.0, 'category' => ContrastReserveCalculator::category($value)]
            : null;
    }

    /**
     * The advice for each nebula filter of the instrument set (all
     * equipment: all filters) on an emission nebula (FilterAdvice::advise()):
     * the contrast reserve at the magnification of the view, and the best
     * magnification of the eyepieces of the set with the filter
     * (FilterAdvice::present()).
     *
     * @param  Collection<int, Eyepiece>  $eyepieces
     * @return list<array<string, mixed>>
     */
    private function filterAdvice(User $user, ?InstrumentSet $set, DeepskyObject $object, Instrument $instrument, ?Lens $lens, Collection $eyepieces, float $sqm, float $extinction, mixed $magnification): array
    {
        $hasMagnitude = is_numeric($object->mag) && (float) $object->mag < 99 && (float) $object->mag != 0.0;
        if (! $hasMagnitude || ! is_numeric($magnification) || FilterAdvice::spectrum($object->type, $object->name) === null) {
            return [];
        }
        $target     = ContrastReserveCalculator::target($object->diam1, $object->diam2, (float) $object->mag + $extinction);
        $lensFactor = ContrastReserveCalculator::lensFactor($lens);
        $options    = (float) $instrument->fixedMagnification > 0
            ? [(int) round((float) $instrument->fixedMagnification) => []]
            : ContrastReserveCalculator::eyepieceMagnifications($instrument, $eyepieces->where('active', true), $lensFactor);
        $options[(int) round((float) $magnification)] ??= [];

        $fields  = ContrastReserveCalculator::fields($instrument, $options, $lensFactor);
        $filters = FilterAdvice::filters($user, $set);

        return FilterAdvice::present(FilterAdvice::advise($target, $object->type, $object->name, $filters, $instrument, $sqm, $options, $fields, (float) $magnification));
    }

    /**
     * The equipment and locations an observer can choose from: those of the
     * instrument set when one is given, with the finders mounted on its
     * telescopes and the lenses always used with them. A set without locations keeps all the locations of the
     * observer.
     *
     * @return array{sets: Collection, instruments: Collection, eyepieces: Collection, lenses: Collection, finders: Collection, locations: Collection}
     */
    public function choices(User $user, ?InstrumentSet $set = null): array
    {
        $instruments = ($set ? $set->instruments() : Instrument::query())
            ->where('instruments.user_id', $user->id)
            ->where('instruments.active', 1)
            ->orderBy('instruments.name')
            ->get()
            ->reject(fn (Instrument $instrument) => $instrument->instrument_type?->name === 'Naked Eye')
            ->values();

        // The finders: those in the set, and those mounted on its telescopes.
        $finders = $instruments->filter(fn (Instrument $instrument) => (float) $instrument->fixedMagnification > 1);
        if ($set) {
            $mounted = Instrument::where('user_id', $user->id)->where('active', 1)->where('fixedMagnification', '>', 1)
                ->whereIn('id', $instruments->pluck('finder_id')->filter())->get();
            $finders = $finders->concat($mounted)->unique('id')->sortBy('name');
        }
        $finders = $finders->values();

        // The lenses: those in the set, and those always used with its telescopes.
        $lenses = ($set ? $set->lenses() : Lens::query())
            ->where('lenses.user_id', $user->id)->where('lenses.active', 1)->orderBy('lenses.name')->get();
        if ($set) {
            $mountedLenses = Lens::where('user_id', $user->id)->where('active', 1)
                ->whereIn('id', $instruments->pluck('lens_id')->filter())->get();
            $lenses = $lenses->concat($mountedLenses)->unique('id')->sortBy('name')->values();
        }

        $locations = $set
            ? $set->locations()->where('locations.user_id', $user->id)->where('locations.active', 1)->orderBy('locations.name')->get()
            : collect();
        if ($locations->isEmpty()) {
            $locations = Location::where('user_id', $user->id)->where('active', 1)->orderBy('name')->get();
        }

        return [
            'sets'        => InstrumentSet::where('user_id', $user->id)->where('active', 1)->orderBy('name')->get(),
            'instruments' => $instruments,
            'finders'     => $finders,
            'eyepieces'   => ($set ? $set->eyepieces() : Eyepiece::query())
                ->where('eyepieces.user_id', $user->id)->where('eyepieces.active', 1)->orderBy('eyepieces.focal_length_mm', 'desc')->get(),
            'lenses'    => $lenses,
            'locations' => $locations,
        ];
    }

    /**
     * The chosen instrument set of the observer; 'all' for none. Without a
     * choice it is a set with their standard telescope: the standard set
     * when that has it, else the first set that has it, else all equipment.
     * Without a standard telescope it is the standard set.
     */
    private function instrumentSet(User $user, mixed $id): ?InstrumentSet
    {
        if ($id === 'all') {
            return null;
        }

        $sets = InstrumentSet::where('user_id', $user->id)->where('active', 1);
        if (is_numeric($id)) {
            return $sets->find((int) $id);
        }

        if (! $user->stdtelescope) {
            return $user->stdinstrumentset ? $sets->find($user->stdinstrumentset) : null;
        }

        return $sets->whereHas('instruments', fn ($query) => $query->where('instruments.id', $user->stdtelescope))
            ->orderByRaw('id = ? desc', [(int) $user->stdinstrumentset])
            ->orderBy('name')
            ->first();
    }

    /**
     * The chosen item when it is one of the choices, else the default, else
     * (when $fallback is set) the first choice.
     */
    private function pick(Collection $choices, mixed $id, mixed $default, bool $fallback = true): mixed
    {
        foreach ([$id, $default] as $candidate) {
            if (is_numeric($candidate) && ($found = $choices->firstWhere('id', (int) $candidate))) {
                return $found;
            }
        }

        return $fallback ? $choices->first() : null;
    }

    private function chosenMoment(mixed $datetime, string $timezone): ?Carbon
    {
        if ($datetime === 'now') {
            return Carbon::now($timezone)->startOfMinute();
        }
        if (is_string($datetime) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $datetime)) {
            try {
                return Carbon::createFromFormat('Y-m-d\TH:i', $datetime, $timezone)->second(0);
            } catch (\Throwable) {
                // no valid moment chosen
            }
        }

        return null;
    }

    /**
     * The date of the evening of the night: the one chosen, else the
     * ephemeris date of the site.
     */
    private function night(mixed $date, string $timezone): string
    {
        if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date) !== false) {
            return $date;
        }

        return EphemerisDate::current($timezone);
    }

    /**
     * The moment the object stands highest during the night that starts on
     * the evening of $night, between the ends of astronomical twilight
     * (nautical twilight when it never gets darker), to the minute.
     *
     * @return array{0: Carbon, 1: string}|null the moment and 'astronomical' or 'nautical';
     *     null when it does not get dark or the object stays below the horizon
     */
    public function highestAtNight(DeepskyObject $object, Location $location, string $night, string $timezone): ?array
    {
        $latitude  = (float) $location->latitude;
        $longitude = (float) $location->longitude;
        $evening   = Carbon::parse($night.' 12:00', $timezone);
        $today     = date_sun_info($evening->getTimestamp(), $latitude, $longitude);
        $tomorrow  = date_sun_info($evening->copy()->addDay()->getTimestamp(), $latitude, $longitude);

        foreach (['astronomical', 'nautical'] as $twilight) {
            $start = $today[$twilight.'_twilight_end']      ?? null;
            $end   = $tomorrow[$twilight.'_twilight_begin'] ?? null;
            if (! is_int($start) || ! is_int($end) || $end <= $start) {
                continue;
            }

            // The local sidereal time advances 360.9856° a day.
            $first      = Carbon::createFromTimestamp($start, 'UTC');
            $sidereal   = Time::meanSiderialTime($first, new GeographicalCoordinates($longitude, $latitude));
            $lstStart   = ($sidereal->hour + $sidereal->minute / 60 + $sidereal->second / 3600) * 15.0;
            $ra         = (float) $object->ra                                                   * 15.0;
            $dec        = (float) $object->decl;
            $altitudeAt = fn (int $timestamp) => FieldViewRenderer::altitude($ra, $dec, $lstStart + 360.98564736629 * ($timestamp - $start) / 86400.0, $latitude);
            // A moment behind the local horizon only wins when the object is never above it
            $horizon = LocalHorizon::forLocation($location);
            $score   = $horizon === null ? $altitudeAt : fn (int $timestamp) => $altitudeAt($timestamp) - ($horizon->isClear($latitude, $lstStart + 360.98564736629 * ($timestamp - $start) / 86400.0 - $ra, $dec) ? 0.0 : 180.0);

            $best = $start;
            for ($t = $start; $t <= $end; $t += self::NIGHT_STEP_MINUTES * 60) {
                if ($score($t) > $score($best)) {
                    $best = $t;
                }
            }
            if ($score($end) > $score($best)) {
                $best = $end;
            }
            // Refine to the minute around the best step.
            for ($t = max($start, $best - self::NIGHT_STEP_MINUTES * 60); $t <= min($end, $best + self::NIGHT_STEP_MINUTES * 60); $t += 60) {
                if ($score($t) > $score($best)) {
                    $best = $t;
                }
            }

            if ($altitudeAt($best) <= 0) {
                return null;
            }

            return [Carbon::createFromTimestamp($best, $timezone)->second(0), $twilight];
        }

        return null;
    }

    /**
     * The eyepiece that gives the optimum detection magnification of the
     * object among the eyepieces that can be chosen, at which the whole
     * object fits in the field (ContrastReserveCalculator::bestMagnification()).
     * Without one, the eyepiece with the lowest magnification.
     */
    private function defaultEyepiece(DeepskyObject $object, Instrument $instrument, ?Lens $lens, ?Location $location, Collection $eyepieces): ?Eyepiece
    {
        $usable = $eyepieces
            ->filter(fn (Eyepiece $eyepiece) => $instrument->magnificationFor($eyepiece, $lens) && $instrument->eyepieceFieldOfViewDeg($eyepiece, $lens))
            ->values();
        if ($usable->isEmpty()) {
            return null;
        }

        $magnifications = $usable->mapWithKeys(fn (Eyepiece $eyepiece) => [$eyepiece->id => $instrument->magnificationFor($eyepiece, $lens)]);

        $best = null;
        if ($location && is_numeric($object->mag) && (float) $object->mag < 99 && (float) $object->diam1 > 0) {
            try {
                $target = ContrastReserveCalculator::target($object->diam1, $object->diam2, $object->mag);
                $sqm    = $location->getSqm();
                $sbobj  = $target->calculateSBObj();
                if (is_numeric($sqm) && $sbobj !== null) {
                    $fields = [];
                    foreach ($usable as $eyepiece) {
                        $magnification          = $magnifications[$eyepiece->id];
                        $fields[$magnification] = max($fields[$magnification] ?? 0.0, $instrument->eyepieceFieldOfViewDeg($eyepiece, $lens));
                    }
                    $best = ContrastReserveCalculator::bestMagnification($target, $sbobj, (float) $sqm, (float) $instrument->aperture_mm, array_keys($fields), $fields);
                }
            } catch (\Throwable) {
                $best = null;
            }
        }

        if ($best) {
            $id = $magnifications->sortBy(fn (int $magnification) => abs($magnification - $best))->keys()->first();

            return $usable->firstWhere('id', $id);
        }

        return $usable->firstWhere('id', $magnifications->sort()->keys()->first());
    }

    /**
     * Whether the instrument mirrors the image this way: as set for it,
     * never for binoculars, whose prisms keep the image upright.
     */
    private function flips(Instrument $instrument, string $setting): bool
    {
        return ! $instrument->isBinocular() && (bool) $instrument->{$setting};
    }

    private function isEquatorial(Instrument $instrument): bool
    {
        return $instrument->mount_type?->name === 'Equatorial';
    }

    private function direction(float $azimuth): string
    {
        $points = [__('N'), __('NE'), __('E'), __('SE'), __('S'), __('SW'), __('W'), __('NW')];

        return $points[(int) round($azimuth / 45) % 8];
    }

    /**
     * An angle in degrees as 2.5° or, below a degree, as 34'.
     */
    public static function formatAngle(float $degrees): string
    {
        if ($degrees >= 1.0) {
            return rtrim(rtrim(number_format($degrees, 1, '.', ''), '0'), '.').'°';
        }

        return ((int) round($degrees * 60))."'";
    }
}
