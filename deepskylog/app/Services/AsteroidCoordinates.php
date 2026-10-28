<?php

namespace App\Services;

use App\Services\Targets\Pluto;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Elliptic;
use deepskylog\AstronomyLibrary\Time;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Positions and magnitudes of the numbered asteroids, calculated with the
 * astronomy library from their orbital elements in
 * `asteroids_orbital_elements` (JPL's ELEMENTS.NUMBR, refreshed weekly by
 * `php artisan astronomy:updateOrbitalElements`), as CometCoordinates does for
 * comets. The magnitude is that of the IAU H-G system, as JPL Horizons gives.
 *
 * An asteroid in DeepskyLog (the `asteroids` table) is named "(4) Vesta",
 * "(100001) 1982 UC3": its number and the name of the elements, which is its
 * designation when it has no name yet.
 */
class AsteroidCoordinates
{
    /** The mean motion of a body at 1 AU, degrees a day (Gauss' constant). */
    private const MEAN_MOTION_1_AU = 0.9856076686;

    /**
     * The name of an asteroid in DeepskyLog, e.g. "(4) Vesta".
     */
    public static function displayName(object $elements): string
    {
        return '('.(int) $elements->number.') '.trim((string) $elements->name);
    }

    /**
     * The orbital elements of a numbered asteroid, or null.
     */
    public static function orbitalElements(?int $number): ?object
    {
        if ($number === null || $number <= 0 || ! Schema::hasTable('asteroids_orbital_elements')) {
            return null;
        }

        return DB::table('asteroids_orbital_elements')->where('number', $number)->first();
    }

    /**
     * The orbital elements of the numbered asteroids matching what an observer
     * typed: a number ("4", "(4)"), a name or designation ("Vesta", "1982 UC3",
     * the start of it), or both ("4 Vesta", "(4) Vesta"). Exact matches come
     * first.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public static function search(string $query, int $limit = 50)
    {
        $query = preg_replace('/\s+/', ' ', trim($query));
        if ($query === '' || ! Schema::hasTable('asteroids_orbital_elements')) {
            return collect();
        }
        $table = fn () => DB::table('asteroids_orbital_elements');

        // A number: "4", "(4)"
        if (preg_match('/^\(?(\d+)\)?$/', $query, $m)) {
            return $table()->where('number', (int) $m[1])->get();
        }
        // A number and a name: "(4) Vesta", "4 Vesta" (but "1982 UC3" is a designation)
        if (preg_match('/^\((\d+)\)\s*(.+)$/', $query, $m) || preg_match('/^(\d+)\s+(.+)$/', $query, $m)) {
            $byNumber = $table()->where('number', (int) $m[1])->where('name', $m[2])->get();
            if ($byNumber->isNotEmpty()) {
                return $byNumber;
            }
            if (str_starts_with($query, '(')) {
                $query = $m[2];
            }
        }

        // A prefix search can use the index on the name; the name itself first
        $exact = $table()->where('name', $query)->orderBy('number')->get();
        $prefix = $table()->where('name', 'like', addcslashes($query, '%_\\').'%')
            ->where('name', '<>', $query)
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $exact->concat($prefix)->take($limit)->values();
    }

    /**
     * The astronomy library target for a row of `asteroids_orbital_elements`,
     * or null when its orbit is not elliptic. Pluto has a magnitude of its own
     * (Targets\Pluto).
     */
    public static function target(object $elements): ?Elliptic
    {
        $a = (float) ($elements->a ?? 0.0);
        $e = (float) ($elements->e ?? 1.0);
        if ($a <= 0.0 || $e < 0.0 || $e >= 1.0 || ! is_numeric($elements->epoch ?? null)) {
            return null;
        }

        // The elements give the mean anomaly at the epoch; the library wants
        // the time of perihelion.
        $meanMotion = self::MEAN_MOTION_1_AU / ($a * sqrt($a));
        $perihelion = Time::fromJd((float) $elements->epoch - ((float) $elements->M) / $meanMotion);

        $target = (int) ($elements->number ?? 0) === Pluto::NUMBER ? new Pluto() : new Elliptic();
        $target->setOrbitalElements($a, $e, (float) $elements->i, (float) $elements->w, (float) $elements->node, $perihelion);
        if (is_numeric($elements->H ?? null)) {
            $target->setHG((float) $elements->H, is_numeric($elements->G ?? null) ? (float) $elements->G : 0.15);
        }

        return $target;
    }

    /**
     * The predicted magnitude of the asteroid, or null when its H is not known.
     */
    public static function magnitude(Elliptic $target, Carbon $date): ?float
    {
        $magnitude = $target->magnitude($date->copy());

        return $magnitude >= 99.0 ? null : $magnitude;
    }

    /**
     * The predicted magnitudes between two dates, one value every $stepDays days.
     *
     * @return array<int, array{date: string, mag: float}>
     */
    public static function lightCurve(Elliptic $target, Carbon $from, Carbon $to, int $stepDays = 5): array
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
     * Calculate the equatorial coordinates (J2000) of the asteroid for the
     * date and the location of the observer, and store them in the target.
     */
    public static function calculate(Elliptic $target, Carbon $date, GeographicalCoordinates $geo, float $height = 0.0): EquatorialCoordinates
    {
        // The elements are referred to the equinox of J2000
        $target->calculateEquatorialCoordinates($date->copy(), $geo, 2451545.0, $height);

        return $target->getEquatorialCoordinatesToday();
    }
}
