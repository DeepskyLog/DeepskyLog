<?php

namespace App\Services;

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
     * Find the orbital elements of a comet by name, e.g. "12P/Pons-Brooks" or
     * "C/2023 A3 (Tsuchinshan-ATLAS)".
     */
    public static function orbitalElements(?string $name): ?object
    {
        $name = preg_replace('/\s+/', ' ', trim((string) $name));
        if ($name === '' || ! Schema::hasTable('comets_orbital_elements')) {
            return null;
        }

        $table = DB::table('comets_orbital_elements');

        return (clone $table)->where('name', $name)->first()
            ?? (clone $table)->where('name', 'like', '%' . $name . '%')->first()
            // Names without the discoverer in parentheses, or with other parentheses
            ?? (clone $table)
                ->whereRaw("LOWER(REPLACE(REPLACE(name, '(', ''), ')', '')) LIKE ?", ['%' . mb_strtolower(preg_replace('/[()]/', '', $name)) . '%'])
                ->first();
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

        if ($e < 1.0) {
            $target = new Elliptic();
            $target->setOrbitalElements($q / (1.0 - $e), $e, $i, $w, $node, $perihelion);
        } elseif ($e == 1.0) {
            $target = new Parabolic();
            $target->setOrbitalElements($q, $i, $w, $node, $perihelion);
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
            $K = $number($part['K'] ?? null, -20.0, 60.0);
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
     * Values outside a plausible range are ignored: older imports stored the
     * mean motion or a julian day in `n`.
     */
    public static function photometry(object $elements): ?array
    {
        $value = function (string $field, float $min, float $max) use ($elements): ?float {
            $v = $elements->$field ?? null;

            return is_numeric($v) && $v >= $min && $v <= $max ? (float) $v : null;
        };
        $H = $value('H', -20.0, 35.0);
        if ($H === null) {
            return null;
        }

        // 10 is the usual coefficient of log r when it is not known
        return [$H, $value('n', 1.0, 60.0) ?? 10.0, $value('phase_coeff', 0.0, 0.1), $value('n_pre', 1.0, 60.0), $value('n_post', 1.0, 60.0)];
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

        return $target->magnitude($date->copy());
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
        $elements = self::orbitalElements($name);
        $target = $elements ? self::target($elements) : null;

        return $target ? self::calculate($target, $date, $geo, $height) : null;
    }
}
