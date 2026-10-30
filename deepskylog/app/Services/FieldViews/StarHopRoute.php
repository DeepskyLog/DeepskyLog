<?php

namespace App\Services\FieldViews;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use SplPriorityQueue;

/**
 * A star-hop route to an object: a chain of stars from a star seen with the
 * naked eye to the object, each next star (and at last the object) close
 * enough to the one before that both stand in the field of the finder.
 *
 * The route is found with A* over the stars that are conspicuous in the
 * finder (HOP_MARGIN brighter than its limit) within MAX_RADIUS_DEG of the
 * object: every hop costs 1, plus FAINT_WEIGHT per magnitude the star is
 * fainter than a naked-eye star, so the route takes the fewest hops and of
 * those the brightest stars. A hop is at most STEP_FRACTION of the field's
 * radius, which keeps the next star inside the field with room to spare when
 * the current one is centred.
 */
class StarHopRoute
{
    /** A hop is at most this part of the radius of the finder's field. */
    public const STEP_FRACTION = 0.8;

    /** The route starts at a star at least this bright, and at least a magnitude above the sky's limit. */
    public const START_MAGNITUDE = 4.0;

    /** The stars to hop along are at least this much brighter than the finder's limit… */
    public const HOP_MARGIN = 1.5;

    /** …and never fainter than this. */
    public const MAX_HOP_MAGNITUDE = 8.0;

    /** Only stars this close to the object are searched. */
    public const MAX_RADIUS_DEG = 30.0;

    /** The extra cost of a hop per magnitude the star is fainter than START_MAGNITUDE. */
    public const FAINT_WEIGHT = 0.12;

    /** Stars closer than this to each other are one star in the finder (and duplicates in the stars table). */
    private const SAME_STAR_DEG = 1.0 / 60.0;

    /** A star this close to the object (or within it) is not a step of the route. */
    private const MIN_TARGET_DISTANCE_DEG = 0.1;

    /** The direction of a hop, as a translation key. */
    public const COMPASS = ['to the north', 'to the north-east', 'to the east', 'to the south-east', 'to the south', 'to the south-west', 'to the west', 'to the north-west'];

    private const SUPERSCRIPTS = ['1' => '¹', '2' => '²', '3' => '³', '4' => '⁴', '5' => '⁵', '6' => '⁶', '7' => '⁷', '8' => '⁸', '9' => '⁹'];

    /**
     * The route to the point, null when no chain of stars reaches it. The
     * first step is the start star, the last the object; every step after
     * the first has the distance (degrees) and direction from the one before.
     * The limits are rounded down to half a magnitude, so routes are cached
     * while the extinction changes.
     *
     * @return list<array{kind: string, ra: float, dec: float, magnitude: ?float, label: ?string, distance: ?float, direction: ?string}>|null
     */
    public static function find(float $raDeg, float $decDeg, float $fieldRadiusDeg, float $finderLimit, float $nelm, float $targetRadiusDeg = 0.0): ?array
    {
        if ($fieldRadiusDeg <= 0) {
            return null;
        }
        $hopLimit   = floor(min(self::MAX_HOP_MAGNITUDE, $finderLimit - self::HOP_MARGIN) * 2) / 2;
        $startLimit = floor(min(self::START_MAGNITUDE, $nelm - 1.0) * 2) / 2;
        $step       = $fieldRadiusDeg * self::STEP_FRACTION;
        $minimum    = max(self::MIN_TARGET_DISTANCE_DEG, $targetRadiusDeg);

        $key = sprintf('star_hop:%.3f:%.3f:%.2f:%.1f:%.1f:%.2f', $raDeg, $decDeg, $step, $hopLimit, $startLimit, $minimum);

        return Cache::remember($key, now()->addMonth(), fn () => self::search($raDeg, $decDeg, $step, max($startLimit, $hopLimit), $startLimit, $minimum)) ?: null;
    }

    /**
     * @return list<array>|false false when there is no route (cached as such)
     */
    private static function search(float $raDeg, float $decDeg, float $step, float $hopLimit, float $startLimit, float $minimum): array|false
    {
        $target = self::vector($raDeg, $decDeg);
        $stars  = self::stars($raDeg, $decDeg, $hopLimit, $minimum);
        if ($stars === []) {
            return false;
        }

        // The stars per cube of the size of a hop, so the neighbours of a
        // star are found in the 27 cubes around it.
        $cosStep = cos(deg2rad($step));
        $cell    = 2 * sin(deg2rad($step) / 2);
        $grid    = [];
        foreach ($stars as $i => $star) {
            $grid[self::cell($star['v'], $cell)][] = $i;
        }

        $open  = new SplPriorityQueue();
        $cost  = [];
        $from  = [];
        $order = PHP_INT_MAX;
        $rest  = [];
        // The hops still needed at least: one, and the distance over a hop.
        $push = function (int $i, float $g) use (&$open, &$cost, &$rest, &$order, $stars, $target, $step) {
            $rest[$i] ??= max(1.0, rad2deg(acos(max(-1.0, min(1.0, self::dot($stars[$i]['v'], $target))))) / $step);
            $cost[$i] = $g;
            // Equal estimates: the star pushed first first.
            $open->insert($i, [-($g + $rest[$i]), $order--]);
        };
        foreach ($stars as $i => $star) {
            if ($star['mag'] <= $startLimit) {
                $push($i, 0.05 * max(0.0, $star['mag']));
            }
        }

        $best = null;
        $done = [];
        while (! $open->isEmpty()) {
            $i = $open->extract();
            if (isset($done[$i])) {
                continue;
            }
            $done[$i] = true;
            $g        = $cost[$i];
            if ($best !== null && $g + $rest[$i] >= $best[0]) {
                break;
            }
            if (self::dot($stars[$i]['v'], $target) >= $cosStep) {
                $best = [$g + 1.0, $i];

                continue;
            }

            [$cx, $cy, $cz] = self::cellIndex($stars[$i]['v'], $cell);
            for ($dx = -1; $dx <= 1; $dx++) {
                for ($dy = -1; $dy <= 1; $dy++) {
                    for ($dz = -1; $dz <= 1; $dz++) {
                        foreach ($grid[($cx + $dx).':'.($cy + $dy).':'.($cz + $dz)] ?? [] as $j) {
                            if (isset($done[$j]) || self::dot($stars[$i]['v'], $stars[$j]['v']) < $cosStep) {
                                continue;
                            }
                            $next = $g + 1.0 + self::FAINT_WEIGHT * max(0.0, $stars[$j]['mag'] - self::START_MAGNITUDE);
                            if (! isset($cost[$j]) || $next < $cost[$j]) {
                                $from[$j] = $i;
                                $push($j, $next);
                            }
                        }
                    }
                }
            }
        }

        if ($best === null) {
            return false;
        }

        $chain = [$best[1]];
        while (isset($from[$chain[0]])) {
            array_unshift($chain, $from[$chain[0]]);
        }

        $route    = [];
        $previous = null;
        foreach ($chain as $n => $i) {
            $star    = $stars[$i];
            $route[] = [
                'kind'      => $n === 0 ? 'start' : 'star',
                'ra'        => $star['ra'],
                'dec'       => $star['dec'],
                'magnitude' => $star['mag'],
                'label'     => $star['label'],
            ] + self::hop($previous, $star['ra'], $star['dec']);
            $previous = $star;
        }
        $route[] = ['kind' => 'target', 'ra' => $raDeg, 'dec' => $decDeg, 'magnitude' => null, 'label' => null] + self::hop($previous, $raDeg, $decDeg);

        return $route;
    }

    /**
     * The distance and direction from the previous step.
     *
     * @return array{distance: ?float, direction: ?string}
     */
    private static function hop(?array $previous, float $ra, float $dec): array
    {
        if ($previous === null) {
            return ['distance' => null, 'direction' => null];
        }

        return [
            'distance'  => round(self::separation($previous['ra'], $previous['dec'], $ra, $dec), 1),
            'direction' => self::COMPASS[(int) round(self::positionAngle($previous['ra'], $previous['dec'], $ra, $dec) / 45.0) % 8],
        ];
    }

    /**
     * The stars within MAX_RADIUS_DEG of the point and brighter than the
     * limit, not within $minimum of it, one per position (the stars table
     * holds some bright stars twice; the one with a name is kept).
     *
     * @return list<array{ra: float, dec: float, mag: float, label: ?string, v: array{0: float, 1: float, 2: float}}>
     */
    private static function stars(float $raDeg, float $decDeg, float $limit, float $minimum): array
    {
        $rows = DB::table('stars')
            ->select(['ra_degrees', 'decl_degrees', 'vmag', 'bayer', 'bayer_suffix', 'flamsteed', 'constellation_code'])
            ->whereNotNull('vmag')
            ->where('vmag', '<=', $limit)
            ->whereBetween('decl_degrees', [max(-90.0, $decDeg - self::MAX_RADIUS_DEG), min(90.0, $decDeg + self::MAX_RADIUS_DEG)]);
        // The right ascensions around the point, unless the circle holds a pole.
        if (abs($decDeg) + self::MAX_RADIUS_DEG < 89.0) {
            $half = rad2deg(asin(min(1.0, sin(deg2rad(self::MAX_RADIUS_DEG)) / cos(deg2rad($decDeg)))));
            $min  = $raDeg - $half;
            $max  = $raDeg + $half;
            if ($min < 0) {
                $rows->where(fn ($q) => $q->where('ra_degrees', '>=', $min + 360.0)->orWhere('ra_degrees', '<=', $max));
            } elseif ($max >= 360.0) {
                $rows->where(fn ($q) => $q->where('ra_degrees', '>=', $min)->orWhere('ra_degrees', '<=', $max - 360.0));
            } else {
                $rows->whereBetween('ra_degrees', [$min, $max]);
            }
        }
        $rows = $rows->get();

        $candidates = [];
        foreach ($rows as $row) {
            $ra       = (float) $row->ra_degrees;
            $dec      = (float) $row->decl_degrees;
            $distance = self::separation($raDeg, $decDeg, $ra, $dec);
            if ($distance > self::MAX_RADIUS_DEG || $distance < $minimum) {
                continue;
            }
            $candidates[] = ['ra' => $ra, 'dec' => $dec, 'mag' => (float) $row->vmag, 'label' => self::label($row), 'v' => self::vector($ra, $dec)];
        }
        // Named stars first, then the brightest.
        usort($candidates, fn ($a, $b) => [$a['label'] === null, $a['mag']] <=> [$b['label'] === null, $b['mag']]);

        $cell  = 2 * sin(deg2rad(self::SAME_STAR_DEG) / 2);
        $cos   = cos(deg2rad(self::SAME_STAR_DEG));
        $grid  = [];
        $stars = [];
        foreach ($candidates as $star) {
            [$cx, $cy, $cz] = self::cellIndex($star['v'], $cell);
            for ($dx = -1; $dx <= 1; $dx++) {
                for ($dy = -1; $dy <= 1; $dy++) {
                    for ($dz = -1; $dz <= 1; $dz++) {
                        foreach ($grid[($cx + $dx).':'.($cy + $dy).':'.($cz + $dz)] ?? [] as $j) {
                            if (self::dot($star['v'], $stars[$j]['v']) >= $cos) {
                                continue 5;
                            }
                        }
                    }
                }
            }
            $grid[$cx.':'.$cy.':'.$cz][] = count($stars);
            $stars[]                     = $star;
        }

        return $stars;
    }

    /** The Bayer letter or Flamsteed number with the constellation, null for other stars. */
    private static function label(object $row): ?string
    {
        $constellation = (string) $row->constellation_code;
        if ($constellation === '') {
            return null;
        }
        $letter = $row->bayer ? (FieldViewRenderer::GREEK_LETTERS[ucfirst(strtolower((string) $row->bayer))] ?? null) : null;
        if ($letter !== null) {
            return $letter.strtr((string) $row->bayer_suffix, self::SUPERSCRIPTS).' '.$constellation;
        }

        return $row->flamsteed ? $row->flamsteed.' '.$constellation : null;
    }

    /** @return array{0: float, 1: float, 2: float} */
    private static function vector(float $raDeg, float $decDeg): array
    {
        $ra  = deg2rad($raDeg);
        $dec = deg2rad($decDeg);

        return [cos($dec) * cos($ra), cos($dec) * sin($ra), sin($dec)];
    }

    private static function dot(array $a, array $b): float
    {
        return $a[0] * $b[0] + $a[1] * $b[1] + $a[2] * $b[2];
    }

    /** @return array{0: int, 1: int, 2: int} */
    private static function cellIndex(array $v, float $cell): array
    {
        return [(int) floor($v[0] / $cell), (int) floor($v[1] / $cell), (int) floor($v[2] / $cell)];
    }

    private static function cell(array $v, float $cell): string
    {
        return implode(':', self::cellIndex($v, $cell));
    }

    private static function separation(float $ra1, float $dec1, float $ra2, float $dec2): float
    {
        return rad2deg(acos(max(-1.0, min(1.0, self::dot(self::vector($ra1, $dec1), self::vector($ra2, $dec2))))));
    }

    /** The position angle (degrees from north through east) of the second point seen from the first. */
    private static function positionAngle(float $ra1, float $dec1, float $ra2, float $dec2): float
    {
        $dRa   = deg2rad($ra2 - $ra1);
        $angle = atan2(sin($dRa), cos(deg2rad($dec1)) * tan(deg2rad($dec2)) - sin(deg2rad($dec1)) * cos($dRa));

        return fmod(rad2deg($angle) + 360.0, 360.0);
    }
}
