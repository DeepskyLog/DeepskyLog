<?php

namespace App\Services\Planets;

use App\Services\News\SkyPositions;
use Illuminate\Support\Facades\DB;

/**
 * The drawing template of a planet on the sketch pad at the eyepiece, as the
 * blank forms of the ALPO and BAA give it: the disk of Venus, Mars, Jupiter
 * or Saturn at the moment, with its flattening (seen at the tilt DE), the
 * terminator of its phase, the polar caps of Mars (MarsFeatures::capEdge(),
 * schematic), the rings of Saturn with the part behind the globe hidden, and
 * short marks of the central meridian beyond the poles of Mars and Jupiter.
 *
 * Everything is given in the sky as points [west, north] in equatorial radii
 * of the planet, so the page turns and mirrors it as the telescope view (N
 * and W of the sketch pad): 'lines' are the outline drawn under the strokes,
 * 'fills' the areas "Fill the disk" paints (a level of white, 0 for the
 * night side), in the order to paint them. The central meridians change by
 * their 'rate' (degrees per hour), so the page writes them for the moment
 * the sketch was begun.
 */
class PlanetSketchTemplate
{
    public const PLANETS = ['venus', 'mars', 'jupiter', 'saturn'];

    /** The brightness of the disk when filled (white = 1). */
    public const LEVELS = ['venus' => 0.85, 'mars' => 0.55, 'jupiter' => 0.7, 'saturn' => 0.7];

    /** The polar caps of Mars when filled. */
    public const CAP_LEVEL = 0.95;

    /** Saturn's rings in planet radii (inner and outer edge) and their brightness when filled: B, then A outside the Cassini division. */
    public const RINGS = ['b' => [1.53, 1.95, 0.75], 'a' => [2.03, PlanetMoons::RING_RADIUS, 0.55]];

    /** The Cassini division drawn as one line, in planet radii. */
    public const CASSINI = 1.99;

    /** The marks of the central meridian beyond the poles, from and to (in polar radii). */
    public const TICK = [1.06, 1.2];

    /** The points of a full ellipse. */
    private const POINTS = 96;

    /** The slug of PLANETS of the name of a list item (as the planets table names it), else null. */
    public static function planetOf(string $name): ?string
    {
        $slug = DB::table('planets')->where('name', trim($name))->value('slug');

        return in_array($slug, self::PLANETS, true) ? $slug : null;
    }

    /**
     * @return array{planet: string, at: int, extent: float, ra: float, dec: float, lines: list<array{points: list<array{0: float, 1: float}>, closed: bool}>, fills: list<array{level: float, points: list<array{0: float, 1: float}>}>, meridians: list<array{label: string, value: float, rate: float}>, details: list<string>}
     */
    public static function for(string $planet, int $timestamp): array
    {
        $disk = PlanetDisk::at($planet, $timestamp);
        // The polar radius as seen at the tilt DE of the disk
        $f   = PlanetDisk::FLATTENING[$planet];
        $b   = sqrt(1 - (1 - $f * $f) * cos(deg2rad($disk['de'] ?? 0.0)) ** 2);
        $p   = deg2rad($disk['p'] ?? 0.0);
        $sky = fn (array $points) => array_map(fn (array $point) => self::round([
            $point[0] * cos($p) - $point[1] * sin($p),
            $point[0] * sin($p) + $point[1] * cos($p),
        ]), $points);

        $limb  = self::ellipse(1.0, $b);
        $night = self::nightSide(deg2rad($disk['bright_limb']) - $p, $disk['phase_angle'], $b);
        $lines = [['points' => $sky($limb), 'closed' => true]];
        $fills = [];
        $globe = [
            ['level' => self::LEVELS[$planet], 'points' => $sky($limb)],
        ];
        $nightFill = ['level' => 0.0, 'points' => $sky($night)];
        if ($disk['phase_angle'] > 0.5) {
            $lines[] = ['points' => $sky($night), 'closed' => true];
        }

        if ($planet === 'mars') {
            $renderer = new PlanetDiskRenderer($disk);
            foreach (['north', 'south'] as $pole) {
                $cap = $renderer->capOutline($pole);
                if ($cap !== null) {
                    $cap     = array_map(fn (array $point) => self::round($point), $cap);
                    $lines[] = ['points' => $cap, 'closed' => true];
                    $globe[] = ['level' => self::CAP_LEVEL, 'points' => $cap];
                }
            }
        }

        if ($planet === 'saturn') {
            // The half of the rings nearer to us lies south of the centre when their northern face is turned to us
            $tilt     = abs(sin(deg2rad($disk['de'])));
            $farNorth = $disk['de'] >= 0 ? 1.0 : -1.0;
            $behind   = fn (float $y) => $y * $farNorth > 0;
            $inGlobe  = fn (float $x, float $y) => $x * $x + ($y / $b) ** 2 < 1.0;
            // Painted from the back: the far half of the rings, the globe, the near half
            $half = fn (bool $back) => array_map(fn (array $ring) => [
                'level'  => $ring[2],
                'points' => $sky(self::halfRing($ring[0], $ring[1], $tilt * $farNorth, $back)),
            ], array_values(self::RINGS));
            $fills = [...$half(true), ...$globe, $nightFill, ...$half(false)];

            // The edges of the rings, without what the globe hides behind it
            foreach ([self::RINGS['b'][0], self::RINGS['b'][1], self::CASSINI, self::RINGS['a'][1]] as $radius) {
                $ring = self::ellipse($radius, $radius * $tilt);
                foreach (self::segments($ring, fn (array $point) => ! ($behind($point[1]) && $inGlobe(...$point))) as $segment) {
                    $lines[] = ['points' => $sky($segment), 'closed' => false];
                }
            }
            // The globe's limb, without what the near half of the rings covers
            $lines[0] = null;
            $covered  = function (array $point) use ($behind, $tilt): bool {
                if ($tilt < 0.02 || $behind($point[1]) || abs($point[1]) < 1e-9) {
                    return false;
                }
                $r = hypot($point[0], $point[1] / $tilt);

                return $r >= self::RINGS['b'][0] && $r <= self::RINGS['a'][1];
            };
            foreach (self::segments($limb, fn (array $point) => ! $covered($point)) as $segment) {
                $lines[] = ['points' => $sky($segment), 'closed' => false];
            }
            $lines = array_values(array_filter($lines));
        } else {
            $fills = [...$globe, $nightFill];
        }

        // The marks of the central meridian, beyond both poles
        if (in_array($planet, ['mars', 'jupiter'], true)) {
            foreach ([1.0, -1.0] as $side) {
                $lines[] = ['points' => $sky([[0.0, $side * $b * self::TICK[0]], [0.0, $side * $b * self::TICK[1]]]), 'closed' => false];
            }
        }

        $diameter = round($disk['diameter'], 1);
        $percent  = round($disk['illuminated'] * 100);
        $details  = match ($planet) {
            'venus'   => [__('Diameter :diameter″, illuminated :percent%', ['diameter' => $diameter, 'percent' => $percent])],
            'mars'    => [__('Diameter :diameter″, illuminated :percent%', ['diameter' => $diameter, 'percent' => $percent]).', Ls '.round($disk['ls']).'°'],
            'jupiter' => [__('Diameter :diameter″', ['diameter' => $diameter])],
            'saturn'  => [__('Diameter :diameter″, ring tilt :tilt°', ['diameter' => $diameter, 'tilt' => round($disk['de'], 1)])],
        };

        return [
            'planet'    => $planet,
            'at'        => $timestamp,
            'extent'    => $planet === 'saturn' ? PlanetMoons::RING_RADIUS : self::TICK[1],
            'ra'        => $disk['ra'],
            'dec'       => $disk['dec'],
            'lines'     => $lines,
            'fills'     => $fills,
            'meridians' => self::meridians($planet, $timestamp, $disk),
            'details'   => $details,
        ];
    }

    /**
     * The central meridians of Mars (areographic) and Jupiter (System I and
     * II) at the moment and how fast they change.
     *
     * @param  array<string, mixed>  $disk  PlanetDisk::at()
     * @return list<array{label: string, value: float, rate: float}>
     */
    private static function meridians(string $planet, int $timestamp, array $disk): array
    {
        $meridian = fn (string $label, float $now, float $hourLater) => [
            'label' => $label,
            'value' => round($now, 2),
            'rate'  => round(SkyPositions::wrap180($hourLater - $now), 4),
        ];

        if ($planet === 'mars') {
            return [$meridian('CM', $disk['cm'], PlanetDisk::at('mars', $timestamp + 3600)['cm'])];
        }
        if ($planet === 'jupiter') {
            $now   = PlanetMoons::jupiter($timestamp);
            $later = PlanetMoons::jupiter($timestamp + 3600);

            return [$meridian('CM I', $now['cm1'], $later['cm1']), $meridian('CM II', $now['cm2'], $later['cm2'])];
        }

        return [];
    }

    /**
     * An ellipse around the centre with semi-axes $a along the equator and
     * $c towards the pole, starting at the west end, north first.
     *
     * @return list<array{0: float, 1: float}>
     */
    private static function ellipse(float $a, float $c): array
    {
        $points = [];
        for ($i = 0; $i < self::POINTS; $i++) {
            $t        = 2 * M_PI * $i / self::POINTS;
            $points[] = [$a * cos($t), $c * sin($t)];
        }

        return $points;
    }

    /**
     * The half of a ring between two radii, as one polygon: the half beyond
     * the planet ($back) or the one in front of it. $tilt is the sine of the
     * tilt, positive when the far half lies to the north.
     *
     * @return list<array{0: float, 1: float}>
     */
    private static function halfRing(float $inner, float $outer, float $tilt, bool $back): array
    {
        $half   = self::POINTS / 2;
        $sign   = $back ? 1.0 : -1.0;
        $points = [];
        for ($i = 0; $i <= $half; $i++) {
            $t        = M_PI * $i / $half;
            $points[] = [$outer * cos($t), $sign * $outer * $tilt * sin($t)];
        }
        for ($i = $half; $i >= 0; $i--) {
            $t        = M_PI * $i / $half;
            $points[] = [$inner * cos($t), $sign * $inner * $tilt * sin($t)];
        }

        return $points;
    }

    /**
     * The unlit part of the disk in the frame of the planet: the limb on the
     * side away from the Sun and the terminator, an ellipse of semi-minor
     * axis cos(phase angle), both flattened to the polar radius $b. $chi is
     * the position angle of the bright limb from the planet's north pole.
     *
     * @return list<array{0: float, 1: float}>
     */
    private static function nightSide(float $chi, float $phaseAngle, float $b): array
    {
        $cosI   = cos(deg2rad($phaseAngle));
        $toSun  = [-sin($chi), cos($chi)];
        $across = [cos($chi), sin($chi)];
        $points = [];
        $add    = function (float $u, float $v) use (&$points, $toSun, $across, $b) {
            $points[] = [$u * $toSun[0] + $v * $across[0], ($u * $toSun[1] + $v * $across[1]) * $b];
        };
        $half = self::POINTS / 2;
        for ($i = 0; $i <= $half; $i++) {
            $a = M_PI / 2 + M_PI * $i / $half;
            $add(cos($a), sin($a));
        }
        for ($i = 0; $i <= $half; $i++) {
            $a = -M_PI / 2 + M_PI * $i / $half;
            $add(-$cosI * cos($a), sin($a));
        }

        return $points;
    }

    /**
     * The runs of a closed outline whose points are visible, each as an open
     * line; one run joined over the start when it wraps around.
     *
     * @param  list<array{0: float, 1: float}>  $points
     * @return list<list<array{0: float, 1: float}>>
     */
    private static function segments(array $points, callable $visible): array
    {
        $count = count($points);
        $shown = array_map($visible, $points);
        if (! in_array(false, $shown, true)) {
            return [[...$points, $points[0]]];
        }

        // Start just after a hidden point, so no run wraps around
        $start = (array_search(false, $shown, true) + 1) % $count;
        $runs  = [];
        $run   = [];
        for ($k = 0; $k <= $count; $k++) {
            $i = ($start + $k) % $count;
            if ($shown[$i] && $k < $count) {
                $run[] = $points[$i];

                continue;
            }
            if (count($run) > 1) {
                $runs[] = $run;
            }
            $run = [];
        }

        return $runs;
    }

    /**
     * @param  array{0: float, 1: float}  $point
     * @return array{0: float, 1: float}
     */
    private static function round(array $point): array
    {
        return [round($point[0], 4), round($point[1], 4)];
    }
}
