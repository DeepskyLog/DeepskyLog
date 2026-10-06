<?php

namespace App\Services\FieldViews;

use App\Models\DeepskyObject;
use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\InstrumentSet;
use App\Models\Lens;
use App\Models\Location;
use App\Models\User;
use App\Models\UserObjectMetric;
use App\Services\TelescopicLimitingMagnitude;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use deepskylog\AstronomyLibrary\Time;
use Illuminate\Support\Collection;

/**
 * The three views of an object: with the naked eye, in the finder of the
 * telescope and in the telescope itself, for an observer's equipment,
 * location and moment.
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
    private const MIRROR_TYPES = ['Reflector', 'Cassegrain', 'Kutter', 'Maksutov', 'Schmidt Cassegrain'];

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
     *     from the defaults: the standard instrument set, telescope and location, and the night
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

        $lst      = null;
        $latitude = null;
        $altitude = null;
        $azimuth  = null;
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
            }
        }

        // Dimming by the atmosphere at the altitude of the object.
        $extinction = $altitude !== null && $altitude > 0 ? FieldViewRenderer::extinction($altitude) : 0.0;

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
                    ?? $this->defaultEyepiece($user, $object, $instrument, $lens, $location, $choices['eyepieces']);
                $telescopeFov = $eyepiece ? $instrument->eyepieceFieldOfViewDeg($eyepiece, $lens) : null;
            }
            $magnification = $instrument->magnificationFor($eyepiece, $lens);
        }

        $finderFov = $finder?->fixedFieldOfViewDeg();
        $up        = $instrument && $this->isEquatorial($instrument) ? 'north' : 'zenith';

        $views = [];

        // With the naked eye
        $nakedEye = $base + [
            'radius_deg'     => self::NAKED_EYE_RADIUS_DEG,
            'star_limit'     => $nelm,
            'up'             => 'zenith',
            'horizon'        => true,
            'extinction'     => true,
            'constellations' => true,
            'star_names'     => true,
            'overlays'       => array_values(array_filter([
                $finderFov ? ['radius_deg' => $finderFov / 2, 'label' => __('Finder')] : null,
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
        if ($finder && $finderFov) {
            $finderLimit     = round(TelescopicLimitingMagnitude::calculate($nelm, (float) $finder->aperture_mm, (float) $finder->fixedMagnification, (float) $finder->obstruction_perc, $this->hasMirrors($finder)) - $extinction, 1);
            $views['finder'] = [
                'title'    => __('Finder'),
                'subtitle' => html_entity_decode($finder->fullName()).($finder->fixedMagnification ? ' · '.(int) $finder->fixedMagnification.'x' : ''),
                'fov'      => self::formatAngle($finderFov).($finder->hasEstimatedFieldOfView() ? ' ('.__('estimated').')' : ''),
                'limit'    => $finderLimit,
                'svg'      => $this->renderer->render($base + [
                    'radius_deg'    => $finderFov / 2,
                    'star_limit'    => $finderLimit,
                    'object_limit'  => $finderLimit - self::OBJECT_LIMIT_OFFSET,
                    'up'            => $up,
                    'flip'          => (bool) $finder->flip_image,
                    'flop'          => (bool) $finder->flop_image,
                    'object_labels' => true,
                    'overlays'      => $telescopeFov ? [['radius_deg' => $telescopeFov / 2, 'label' => '']] : [],
                ]),
            ];
        } else {
            $views['finder'] = [
                'title'   => __('Finder'),
                'message' => $instrument
                    ? __('No finderscope is coupled to :instrument. Choose one above, or set it on the instrument page.', ['instrument' => html_entity_decode($instrument->fullName())])
                    : __('Choose a finderscope above.'),
            ];
        }

        // In the telescope
        if ($instrument && $telescopeFov) {
            $telescopeLimit = round(TelescopicLimitingMagnitude::calculate($nelm, (float) $instrument->aperture_mm, $magnification ? (float) $magnification : null, (float) $instrument->obstruction_perc, $this->hasMirrors($instrument)) - $extinction, 1);
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
                'title'    => __('Telescope'),
                'subtitle' => $subtitle,
                'fov'      => self::formatAngle($telescopeFov),
                'limit'    => $telescopeLimit,
                'svg'      => $this->renderer->render($base + [
                    'radius_deg'    => $telescopeFov / 2,
                    'star_limit'    => $telescopeLimit,
                    'object_limit'  => $telescopeLimit - self::OBJECT_LIMIT_OFFSET,
                    'up'            => $up,
                    'flip'          => (bool) $instrument->flip_image,
                    'flop'          => (bool) $instrument->flop_image,
                    'object_labels' => true,
                ]),
            ];
        } else {
            $views['telescope'] = [
                'title'   => __('Telescope'),
                'message' => ! $instrument
                    ? __('Add an instrument to see the view through the telescope.')
                    : __('The field of view is unknown: choose an eyepiece with a focal length and an apparent field of view or field stop.'),
            ];
        }

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
            'warnings'    => $warnings,
        ];
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
     * The chosen instrument set of the observer; 'all' for none, and
     * without a choice their standard set.
     */
    private function instrumentSet(User $user, mixed $id): ?InstrumentSet
    {
        if ($id === 'all') {
            return null;
        }

        $id = is_numeric($id) ? (int) $id : $user->stdinstrumentset;

        return $id ? InstrumentSet::where('user_id', $user->id)->where('active', 1)->find($id) : null;
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

            $best = $start;
            for ($t = $start; $t <= $end; $t += self::NIGHT_STEP_MINUTES * 60) {
                if ($altitudeAt($t) > $altitudeAt($best)) {
                    $best = $t;
                }
            }
            if ($altitudeAt($end) > $altitudeAt($best)) {
                $best = $end;
            }
            // Refine to the minute around the best step.
            for ($t = max($start, $best - self::NIGHT_STEP_MINUTES * 60); $t <= min($end, $best + self::NIGHT_STEP_MINUTES * 60); $t += 60) {
                if ($altitudeAt($t) > $altitudeAt($best)) {
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
     * The eyepiece that gives the magnification closest to the optimum
     * detection magnification of the object: the one stored for this
     * equipment and location, else calculated with the astronomy library.
     * Without one, the eyepiece with the lowest magnification.
     */
    private function defaultEyepiece(User $user, DeepskyObject $object, Instrument $instrument, ?Lens $lens, ?Location $location, Collection $eyepieces): ?Eyepiece
    {
        $usable = $eyepieces
            ->filter(fn (Eyepiece $eyepiece) => $instrument->magnificationFor($eyepiece, $lens) && $instrument->eyepieceFieldOfViewDeg($eyepiece, $lens))
            ->values();
        if ($usable->isEmpty()) {
            return null;
        }

        $magnifications = $usable->mapWithKeys(fn (Eyepiece $eyepiece) => [$eyepiece->id => $instrument->magnificationFor($eyepiece, $lens)]);

        $best = UserObjectMetric::where('user_id', $user->id)
            ->where('instrument_id', $instrument->id)
            ->where('location_id', $location?->id)
            ->where('lens_id', $lens?->id)
            ->where('object_name', $object->name)
            ->value('optimum_detection_magnification');

        if (! $best && $location && (float) $object->mag > 0 && (float) $object->mag < 99 && (float) $object->diam1 > 0) {
            try {
                $target = new AstroTarget();
                $target->setDiameter((float) $object->diam1, (float) ($object->diam2 ?: $object->diam1));
                $target->setMagnitude((float) $object->mag);
                $sqm   = $location->getSqm();
                $sbobj = $target->calculateSBObj();
                if ($sqm !== null && $sbobj !== null) {
                    $best = $target->calculateBestMagnification($sbobj, (float) $sqm, (float) $instrument->aperture_mm, array_values(array_unique($magnifications->all())));
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

    private function isEquatorial(Instrument $instrument): bool
    {
        return $instrument->mount_type?->name === 'Equatorial';
    }

    private function hasMirrors(Instrument $instrument): bool
    {
        return in_array($instrument->instrument_type?->name, self::MIRROR_TYPES, true);
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
