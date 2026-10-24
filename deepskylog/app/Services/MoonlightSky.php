<?php

namespace App\Services;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Moon;

/**
 * The brightness of the sky lit by the Moon, with the model of Krisciunas and
 * Schaefer (1991, PASP 103, 1033): the light of the Moon, scattered by the
 * atmosphere towards a point of the sky, depends on the phase of the Moon,
 * its altitude, the altitude of the point and their distance. It is added to
 * the brightness of the dark sky, so a dark site with an SQM of 21.5 gets
 * about 18 near the full Moon.
 *
 * The positions of the Moon during a night are calculated once, every
 * STEP_MINUTES; sqmAt() then only needs a little trigonometry per object.
 */
class MoonlightSky
{
    public const STEP_MINUTES = 30;

    /** Extinction coefficient in V (magnitudes per air mass) of an average site. */
    private const EXTINCTION = 0.2;

    /**
     * The position (degrees) and illuminated fraction of the Moon at each
     * step of the window, keyed by timestamp.
     *
     * @var array<int, array{ra: float, dec: float, altitude: float, fraction: float}>
     */
    private array $moon = [];

    public function __construct(private NightWindow $window, float $longitude, float $elevation = 0.0)
    {
        $geo = new GeographicalCoordinates($longitude, $window->latitude);
        foreach ($this->moments() as $timestamp) {
            $date = Carbon::createFromTimestamp($timestamp, 'UTC');
            $body = new Moon();
            $body->calculateEquatorialCoordinates($date->copy(), $geo, $elevation);
            $coordinates = $body->getEquatorialCoordinatesToday();
            $ra          = (float) $coordinates->getRA()->getCoordinate() * 15.0;
            $dec         = (float) $coordinates->getDeclination()->getCoordinate();

            $this->moon[$timestamp] = [
                'ra'       => $ra,
                'dec'      => $dec,
                'altitude' => $window->altitude($ra, $dec, $timestamp),
                'fraction' => $body->illuminatedFraction($date->copy()),
            ];
        }
    }

    /**
     * The moments the Moon is known at: every STEP_MINUTES from the start of
     * the window, and its end.
     *
     * @return list<int>
     */
    public function moments(): array
    {
        $moments = range($this->window->start, $this->window->end, self::STEP_MINUTES * 60);
        if (end($moments) !== $this->window->end) {
            $moments[] = $this->window->end;
        }

        return $moments;
    }

    /**
     * Whether the Moon is above the horizon at some moment of the window.
     */
    public function isUp(): bool
    {
        foreach ($this->moon as $moon) {
            if ($moon['altitude'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * The illuminated fraction of the Moon in the middle of the window.
     */
    public function illuminatedFraction(): float
    {
        $values = array_values($this->moon);

        return $values[intdiv(count($values), 2)]['fraction'];
    }

    /**
     * The first and last moment of the window the Moon is above the horizon,
     * or null when it stays below.
     *
     * @return array{0: int, 1: int}|null
     */
    public function upBetween(): ?array
    {
        $up = array_keys(array_filter($this->moon, fn (array $moon) => $moon['altitude'] > 0));

        return $up === [] ? null : [min($up), max($up)];
    }

    /**
     * The moment of the window the sky at the point is darkest while the
     * point stands at least $minAltitude high (the higher of equally dark
     * moments), with the sky brightness then; null when it never gets that
     * high.
     *
     * @return array{timestamp: int, sqm: float, altitude: float}|null
     */
    public function darkestMoment(float $raDeg, float $decDeg, float $darkSqm, float $minAltitude): ?array
    {
        $best = null;
        foreach ($this->moments() as $timestamp) {
            $altitude = $this->window->altitude($raDeg, $decDeg, $timestamp);
            if ($altitude < $minAltitude) {
                continue;
            }
            $sqm = round($this->sqmAt($timestamp, $raDeg, $decDeg, $darkSqm), 2);
            if ($best === null || [$sqm, $altitude] > [$best['sqm'], $best['altitude']]) {
                $best = ['timestamp' => $timestamp, 'sqm' => $sqm, 'altitude' => $altitude];
            }
        }

        return $best;
    }

    /**
     * The sky brightness (magnitudes per square arcsecond) at the point of the
     * sky at one of the moments(), from the brightness of the dark sky.
     */
    public function sqmAt(int $timestamp, float $raDeg, float $decDeg, float $darkSqm): float
    {
        $moon = $this->moon[$timestamp] ?? null;
        if ($moon === null || $moon['altitude'] <= 0) {
            return $darkSqm;
        }
        $altitude = $this->window->altitude($raDeg, $decDeg, $timestamp);
        if ($altitude <= 0) {
            return $darkSqm;
        }

        return self::toSqm(self::toNanoLambert($darkSqm) + self::moonlight($moon['fraction'], 90.0 - $moon['altitude'], 90.0 - $altitude, self::separation($raDeg, $decDeg, $moon['ra'], $moon['dec'])));
    }

    /**
     * The brightness of the moonlight (nanolamberts) scattered towards a
     * point at zenith distance $zenith, $distance degrees from a Moon at
     * zenith distance $moonZenith (Krisciunas and Schaefer, eq. 15–21).
     */
    public static function moonlight(float $fraction, float $moonZenith, float $zenith, float $distance): float
    {
        // The phase angle of the Moon, 0 at full Moon, 180 at new Moon.
        $phase       = rad2deg(acos(max(-1.0, min(1.0, 2.0 * $fraction - 1.0))));
        $illuminance = 10 ** (-0.4 * (3.84 + 0.026 * $phase + 4e-9 * $phase ** 4));
        // Rayleigh and Mie scattering; the formula is only valid beyond 10°.
        $distance   = max(10.0, $distance);
        $scattering = 10 ** 5.36 * (1.06 + cos(deg2rad($distance)) ** 2) + 10 ** (6.15 - $distance / 40.0);
        $airMass    = fn (float $z) => 1.0 / sqrt(1.0 - 0.96 * sin(deg2rad($z)) ** 2);

        return $scattering * $illuminance
                           * 10 ** (-0.4 * self::EXTINCTION * $airMass($moonZenith))
                           * (1.0 - 10 ** (-0.4 * self::EXTINCTION * $airMass($zenith)));
    }

    /** Magnitudes per square arcsecond to nanolamberts (Garstang). */
    public static function toNanoLambert(float $sqm): float
    {
        return 34.08 * exp(20.7233 - 0.92104 * $sqm);
    }

    /** Nanolamberts to magnitudes per square arcsecond. */
    public static function toSqm(float $nanoLambert): float
    {
        return (20.7233 - log($nanoLambert / 34.08)) / 0.92104;
    }

    /**
     * The angular distance in degrees between two points of the sky.
     */
    public static function separation(float $ra1, float $dec1, float $ra2, float $dec2): float
    {
        $cos = sin(deg2rad($dec1)) * sin(deg2rad($dec2)) + cos(deg2rad($dec1)) * cos(deg2rad($dec2)) * cos(deg2rad($ra1 - $ra2));

        return rad2deg(acos(max(-1.0, min(1.0, $cos))));
    }
}
