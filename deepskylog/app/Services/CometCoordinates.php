<?php

namespace App\Services;

use App\Services\Comets\HyperbolicOrbit;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Elliptic;
use deepskylog\AstronomyLibrary\Targets\NearParabolic;
use deepskylog\AstronomyLibrary\Targets\Parabolic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Positions of comets, calculated with the astronomy library from the orbital
 * elements in `comets_orbital_elements` (filled by
 * `php artisan astronomy:updateOrbitalElements`).
 *
 * This replaces the queries to JPL Horizons: a Horizons query takes 1 to 2
 * seconds, the calculation about a millisecond. With current elements the
 * positions agree with Horizons to about an arcminute; the difference grows
 * slowly as the elements get older, because the library uses a two-body
 * orbit. The positions are astrometric J2000 coordinates, like the deep-sky
 * objects and the star charts.
 */
class CometCoordinates
{
    /**
     * Elements of comets at the epochs of their observations, filled by
     * `php artisan comets:fetch-epoch-elements`.
     */
    public const EPOCH_TABLE = 'comet_epoch_elements';

    /** The columns that describe the orbit, the same in both tables. */
    public const ORBIT_FIELDS = ['epoch', 'q', 'e', 'i', 'w', 'node', 'Tp'];

    /** From this eccentricity on, an orbit is solved as near-parabolic (see target()). */
    public const NEAR_PARABOLIC_FROM = 0.99;

    /**
     * From this eccentricity on, an orbit is solved as hyperbolic
     * (HyperbolicOrbit): the near-parabolic series does not converge for a
     * clearly hyperbolic orbit (3I/ATLAS, e = 6.1, gave NaN). Up to e = 1.1
     * both agree to better than 0.0001'.
     */
    public const HYPERBOLIC_FROM = 1.01;

    private static ?bool $hasEpochTable = null;

    /**
     * Find the orbital elements of a comet by name, e.g. "12P/Pons-Brooks" or
     * "C/2023 A3 (Tsuchinshan-ATLAS)".
     *
     * Names that are written differently ("C/2023 A3 Tsuchinshan-ATLAS") are
     * matched on their designation, or, without a designation, on the whole
     * name of the discoverers ("Tsuchinshan-ATLAS"). Only a unique match is
     * returned: a part of a name matches other comets too ("12P" is in "112P",
     * "Nishimura" in "Nakamura-Nishimura-Machholz", and two comets are called
     * "Nishimura"),
     * and no position is better than the position of another comet.
     */
    public static function orbitalElements(?string $name, ?Carbon $date = null): ?object
    {
        $current = self::currentElements($name);

        return $date === null ? $current : self::atEpoch($current, (string) $name, $date);
    }

    /**
     * The elements of `comets_orbital_elements` with the orbit replaced by
     * that of the row of `comet_epoch_elements` (JPL Horizons) whose epoch is
     * nearest to the date, when it is nearer than the epoch of the current
     * elements. The current elements describe today's orbit; a comet that
     * passed a planet since moved on another one (C/2007 N3 in January 2009
     * stood 4.6 degrees from where the elements of 2025 put it). The
     * photometry stays that of the current elements.
     */
    public static function atEpoch(?object $current, string $name, Carbon $date): ?object
    {
        if (self::$hasEpochTable === null) {
            self::$hasEpochTable = Schema::hasTable(self::EPOCH_TABLE);
        }
        [$designation] = self::splitName((string) ($current->name ?? $name));
        if (! self::$hasEpochTable || $designation === null) {
            return $current;
        }

        $jd = self::julianDay($date);
        $row = DB::table(self::EPOCH_TABLE)
            ->where('designation', $designation)
            ->orderByRaw('ABS(epoch - ?)', [$jd])
            ->first();
        if ($row === null) {
            return $current;
        }
        if ($current !== null && is_numeric($current->epoch ?? null)
            && abs((float) $current->epoch - $jd) <= abs((float) $row->epoch - $jd)) {
            return $current;
        }

        $elements = $current !== null ? clone $current : (object) [
            'name' => $name, 'H' => null, 'n' => null, 'phase_coeff' => null,
            'n_pre' => null, 'n_post' => null, 'light_curve' => null, 'ref' => $row->source,
        ];
        foreach (self::ORBIT_FIELDS as $field) {
            $elements->{$field} = $row->{$field};
        }
        $elements->ref = $row->source;

        return $elements;
    }

    /**
     * The Julian day of a moment.
     */
    public static function julianDay(Carbon $date): float
    {
        return $date->getTimestamp() / 86400.0 + 2440587.5;
    }

    /**
     * The current elements of a comet, see orbitalElements().
     */
    private static function currentElements(?string $name): ?object
    {
        $name = preg_replace('/\s+/', ' ', trim((string) $name));
        if ($name === '' || ! Schema::hasTable('comets_orbital_elements')) {
            return null;
        }

        $table = DB::table('comets_orbital_elements');
        if ($row = (clone $table)->where('name', $name)->first()) {
            return $row;
        }

        [$designation, $discoverers] = self::splitName($name);
        $needle = $designation ?? $discoverers;
        if ($needle === '') {
            return null;
        }
        $candidates = (clone $table)
            // (only a first selection, the names are compared below)
            ->where('name', 'like', '%' . $needle . '%')
            ->get()
            ->map(fn (object $row) => [$row, ...self::splitName((string) $row->name)]);

        $unique = function ($matches) {
            return $matches->count() === 1 ? $matches->first()[0] : null;
        };

        if ($designation === null) {
            return $unique($candidates->filter(fn (array $c) => $c[2] === $discoverers));
        }

        $sameDesignation = $candidates->filter(fn (array $c) => $c[1] === $designation);

        return $unique($sameDesignation->filter(fn (array $c) => $c[2] === $discoverers))
            ?? $unique($sameDesignation);
    }

    /**
     * The designation (upper case, null when the name has none) and the name
     * of the discoverers (lower case, without parentheses) of a comet name,
     * e.g. ["C/2023 A3", "tsuchinshan-atlas"] or ["12P", "pons-brooks"].
     *
     * @return array{0: ?string, 1: string}
     */
    public static function splitName(string $name): array
    {
        $name = preg_replace('/\s+/', ' ', trim($name));
        $designation = null;
        // Numbered periodic comets (12P, 73P-B) and provisional designations
        // (C/2023 A3, P/2010 H5, D/1993 F2-P1, C/-43 K1)
        if (preg_match('~^(\d+[PDI](?:-[A-Z]{1,2}\d*)?|[PCDXAI]/-?\d{1,4} [A-Z]{1,2}\d*(?:-[A-Z]{1,2}\d*)?)(?=[ /]|$)~i', $name, $m)) {
            $designation = mb_strtoupper($m[1]);
            $name = substr($name, strlen($m[1]));
        }
        $discoverers = trim(preg_replace('/\s+/', ' ', str_replace(['(', ')'], ' ', ltrim($name, ' /'))));

        return [$designation, mb_strtolower($discoverers)];
    }

    /**
     * The time of perihelion of JPL's element files, YYYYMMDD.dddd (TDB), as a date.
     */
    public static function perihelionDate(mixed $tp): ?Carbon
    {
        if (! is_numeric($tp)) {
            return null;
        }
        $tp = (float) $tp;
        $ymd = (int) floor($tp);
        $year = intdiv($ymd, 10000);
        $month = intdiv($ymd, 100) % 100;
        $day = $ymd % 100;
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return null;
        }

        return Carbon::create($year, $month, $day, 0, 0, 0, 'UTC')
            ->addSeconds((int) round(($tp - $ymd) * 86400));
    }

    /**
     * The astronomy library target for a row of `comets_orbital_elements`,
     * with the photometric parameters of the comet when they are known.
     */
    public static function target(object $elements): Elliptic|Parabolic|NearParabolic|null
    {
        $perihelion = self::perihelionDate($elements->Tp ?? null);
        if ($perihelion === null || ! is_numeric($elements->q ?? null) || ! is_numeric($elements->e ?? null)) {
            return null;
        }
        $q = (float) $elements->q;
        $e = (float) $elements->e;
        $i = (float) ($elements->i ?? 0.0);
        $w = (float) ($elements->w ?? 0.0);
        $node = (float) ($elements->node ?? 0.0);

        // The library's Elliptic loses its precision for an eccentricity close
        // to 1 (a semi-major axis of thousands of AU): 2' at 0.999, a degree at
        // 0.9999, tens of degrees beyond (C/2007 N3 with JPL's e of 0.9999993
        // landed 10 degrees from its place). The near-parabolic solution of
        // Meeus chapter 35 holds on both sides of 1 and agrees with Elliptic to
        // 0.4' at 0.99. A clearly hyperbolic orbit gets the hyperbolic
        // Kepler equation (HyperbolicOrbit, a NearParabolic).
        if ($e < self::NEAR_PARABOLIC_FROM) {
            $target = new Elliptic();
            $target->setOrbitalElements($q / (1.0 - $e), $e, $i, $w, $node, $perihelion);
        } elseif ($e == 1.0) {
            $target = new Parabolic();
            $target->setOrbitalElements($q, $i, $w, $node, $perihelion);
        } elseif ($e >= self::HYPERBOLIC_FROM) {
            $target = new HyperbolicOrbit();
            $target->setOrbitalElements($q, $e, $i, $w, $node, $perihelion);
        } else {
            $target = new NearParabolic();
            $target->setOrbitalElements($q, $e, $i, $w, $node, $perihelion);
        }

        $photometry = self::photometry($elements);
        // Elliptic has comet photometry since version 6.8.1 of the astronomy
        // library; before that it only knew the H-G system of asteroids.
        if ($photometry !== null && method_exists($target, 'hasCometParams')) {
            $target->setCometParams(...$photometry);
        }
        // The light curve of aerith.net in parts, since version 6.8.3; it takes
        // precedence over H and n.
        $parts = self::lightCurveParts($elements);
        if (! empty($parts) && method_exists($target, 'setCometLightCurve')) {
            $target->setCometLightCurve($parts);
        }

        return $target;
    }

    /**
     * The light curve of aerith.net in parts, each valid for a range of days
     * around the perihelion, from the `light_curve` column.
     *
     * @return array<int, array{from: ?float, to: ?float, H: float, K: float, shift: float}>
     */
    public static function lightCurveParts(object $elements): array
    {
        $parts = $elements->light_curve ?? null;
        if (is_string($parts)) {
            $parts = json_decode($parts, true);
        }
        if (! is_array($parts)) {
            return [];
        }

        $number = fn ($v, float $min, float $max) => is_numeric($v) && $v >= $min && $v <= $max ? (float) $v : null;
        $valid = [];
        foreach ($parts as $part) {
            $H = $number($part['H'] ?? null, -20.0, 35.0);
            $K = $number($part['K'] ?? null, -20.0, 150.0);
            if ($H === null || $K === null) {
                continue;
            }
            $valid[] = [
                'from' => is_numeric($part['from'] ?? null) ? (float) $part['from'] : null,
                'to' => is_numeric($part['to'] ?? null) ? (float) $part['to'] : null,
                'H' => $H,
                'K' => $K,
                'shift' => is_numeric($part['shift'] ?? null) ? (float) $part['shift'] : 0.0,
            ];
        }

        return $valid;
    }

    /**
     * The photometric parameters of a row of `comets_orbital_elements`, as
     * arguments for setCometParams(): the total magnitude is
     * m = H + 5 log(delta) + n log(r) + phase_coeff * alpha, with `n` the
     * coefficient of log r as on aerith.net and in JPL's K1.
     *
     * Values outside a plausible range are ignored: older imports stored a
     * julian day in `n`. Without a plausible `n` there is no photometry: H
     * only means something with its own coefficient, and a default one gives
     * absurd magnitudes for the steep curves of aerith.net
     * (m1 = -14.5 + 5 log d + 100 log r would become -3 for a faint comet).
     */
    public static function photometry(object $elements): ?array
    {
        $value = function (string $field, float $min, float $max) use ($elements): ?float {
            $v = $elements->$field ?? null;

            return is_numeric($v) && $v >= $min && $v <= $max ? (float) $v : null;
        };
        $H = $value('H', -20.0, 35.0);
        $n = $value('n', -20.0, 150.0);
        if ($H === null || $n === null) {
            return null;
        }

        return [$H, $n, $value('phase_coeff', 0.0, 0.1), $value('n_pre', 1.0, 60.0), $value('n_post', 1.0, 60.0)];
    }

    /**
     * The predicted total magnitude of the comet, or null when its photometry
     * is not known (or the installed astronomy library cannot calculate it).
     */
    public static function magnitude(Elliptic|Parabolic|NearParabolic $target, Carbon $date): ?float
    {
        if (! method_exists($target, 'hasCometParams') || ! $target->hasCometParams()) {
            return null;
        }

        $magnitude = $target->magnitude($date->copy());

        // A position the library could not solve gives NaN, which would pass
        // every "brighter than" test and break the JSON of the pages.
        return is_finite($magnitude) ? $magnitude : null;
    }

    /**
     * The predicted magnitudes between two dates, one value every $stepDays days.
     *
     * @return array<int, array{date: string, mag: float}>
     */
    public static function lightCurve(Elliptic|Parabolic|NearParabolic $target, Carbon $from, Carbon $to, int $stepDays = 5): array
    {
        $points = [];
        for ($date = $from->copy(); $date <= $to; $date->addDays($stepDays)) {
            $mag = self::magnitude($target, $date);
            if ($mag === null) {
                return [];
            }
            $points[] = ['date' => $date->toDateString(), 'mag' => $mag];
        }

        return $points;
    }

    /**
     * Calculate the equatorial coordinates of the comet for the date and the
     * location of the observer, and store them in the target.
     */
    public static function calculate(Elliptic|Parabolic|NearParabolic $target, Carbon $date, GeographicalCoordinates $geo, float $height = 0.0): EquatorialCoordinates
    {
        // Older versions of the astronomy library move the date given to them.
        $date = $date->copy();
        if ($target instanceof Elliptic) {
            // The elements are referred to the equinox of J2000
            $target->calculateEquatorialCoordinates($date, $geo, 2451545.0, $height);
        } else {
            $target->calculateEquatorialCoordinates($date, $geo, $height);
        }

        return $target->getEquatorialCoordinatesToday();
    }

    /**
     * The equatorial coordinates of a comet, looked up by name, or null when
     * there are no usable orbital elements for it.
     */
    public static function forName(?string $name, Carbon $date, GeographicalCoordinates $geo, float $height = 0.0): ?EquatorialCoordinates
    {
        $elements = self::orbitalElements($name, $date);
        $target = $elements ? self::target($elements) : null;

        return $target ? self::calculate($target, $date, $geo, $height) : null;
    }
}
