<?php

namespace App\Services;

/**
 * The order to observe fixed points of the sky in during a window of a night
 * (NightWindow), each for $minutes, while it stands at least $minAltitude
 * high. Used by the Messier marathon planner and the times of an observing
 * list.
 *
 * The moments a point crosses the altitude follow from the hour angle, as in
 * SeasonalVisibility, so thousands of objects take milliseconds. Three
 * orders are tried, each taking at every moment one of the objects that are
 * up: the one that sets first (earliest deadline first, which never misses
 * an object when a schedule without misses exists), the same preferring
 * objects SOFT_HOURS past their transit (so objects that stay up long do not
 * all wait for the morning), and the one nearest to its highest moment (for
 * a list with more objects than the night has time for). The order that
 * misses the fewest objects wins, then the one that sees them highest. Each
 * object is then moved, in the same order, towards the moment it stands
 * highest, keeping at least half the time it could wait.
 */
class NightPlanner
{
    /** An object is preferred this long after its transit. */
    private const SOFT_HOURS = 3;

    public function __construct(
        public readonly float $latitude,
        public readonly float $minAltitude,
        public readonly int $minutes,
    ) {
    }

    /**
     * Per point (RA and declination in degrees, keys 'ra' and 'dec'), the
     * parts of the window (timestamps) it stands at least $minAltitude high,
     * and its transits in the window.
     *
     * @param  array<int, array{ra: float, dec: float}>  $points
     * @return array<int, array{intervals: list<array{0: int, 1: int}>, transits: list<int>}>
     */
    public function visibility(NightWindow $window, array $points): array
    {
        $span    = $window->siderealTime($window->end) - $window->lstStart;
        $seconds = $window->end                        - $window->start;
        $moment  = fn (float $hourAngle, float $from) => $window->start + (int) round(($hourAngle - $from) / $span * $seconds);
        $lat     = deg2rad($this->latitude);

        $visibility = [];
        foreach ($points as $key => $point) {
            $dec      = deg2rad($point['dec']);
            $cosLimit = (sin(deg2rad($this->minAltitude)) - sin($lat) * sin($dec)) / max(1e-12, cos($lat) * cos($dec));
            $from     = fmod(fmod($window->lstStart - $point['ra'], 360.0) + 360.0, 360.0);
            $to       = $from + $span;

            // The point is high enough for hour angles within the limit of a
            // transit; the window spans less than a day, so it meets at most
            // the transits at hour angle 0, 360 and 720.
            $intervals = [];
            $transits  = [];
            if ($cosLimit < 1.0) {
                $limit = $cosLimit <= -1.0 ? 180.0 : rad2deg(acos($cosLimit));
                foreach ([0.0, 360.0, 720.0] as $transit) {
                    $a = max($from, $transit - $limit);
                    $b = min($to, $transit + $limit);
                    if ($b <= $a) {
                        continue;
                    }
                    $interval = [$moment($a, $from), $moment($b, $from)];
                    if ($intervals !== [] && $interval[0] <= end($intervals)[1] + 1) {
                        $intervals[count($intervals) - 1][1] = $interval[1];
                    } else {
                        $intervals[] = $interval;
                    }
                    if ($transit >= $from && $transit <= $to) {
                        $transits[] = $moment($transit, $from);
                    }
                }
            }

            $visibility[$key] = ['intervals' => $intervals, 'transits' => $transits];
        }

        return $visibility;
    }

    /**
     * The order of the points (keys of $visibility and $points): for each
     * the moment to observe it and the interval of $visibility it is in, and
     * the points it misses.
     *
     * @param  array<int, array{intervals: list<array{0: int, 1: int}>, transits: list<int>}>  $visibility
     * @param  array<int, array{ra: float, dec: float}>  $points
     * @return array{order: list<array{0: int, 1: int, 2: array{0: int, 1: int}}>, missed: list<int>}
     */
    public function schedule(NightWindow $window, array $visibility, array $points): array
    {
        $best = null;
        foreach (['soft', 'plain', 'transit'] as $mode) {
            $schedule          = $this->order($window, $visibility, $points, $mode);
            $schedule['order'] = $this->spread($window, $schedule['order'], $visibility, $points);
            $altitude          = $schedule['order'] === [] ? 0.0 : array_sum(array_map(fn (array $item) => $window->altitude($points[$item[0]]['ra'], $points[$item[0]]['dec'], $item[1]), $schedule['order'])) / count($schedule['order']);
            $rank              = [count($schedule['missed']), -$altitude];
            if ($best === null || $rank < $best['rank']) {
                $best = ['rank' => $rank, 'schedule' => $schedule];
            }
        }

        return $best['schedule'];
    }

    /**
     * The moment the point stands highest in the interval.
     */
    private function highest(NightWindow $window, array $point, array $transits, int $from, int $until): int
    {
        foreach ($transits as $transit) {
            if ($transit >= $from && $transit <= $until) {
                return $transit;
            }
        }

        return $window->altitude($point['ra'], $point['dec'], $from) >= $window->altitude($point['ra'], $point['dec'], $until) ? $from : $until;
    }

    /**
     * Move each point of the order towards the moment it stands highest in
     * its interval, keeping the order and at least half the time it could
     * wait while every point after it is still seen within its interval.
     *
     * @param  list<array{0: int, 1: int, 2: array{0: int, 1: int}}>  $order
     * @return list<array{0: int, 1: int, 2: array{0: int, 1: int}}>
     */
    private function spread(NightWindow $window, array $order, array $visibility, array $points): array
    {
        $slot   = $this->minutes * 60;
        $latest = [];
        for ($i = count($order) - 1; $i >= 0; $i--) {
            $latest[$i] = min($order[$i][2][1], isset($latest[$i + 1]) ? $latest[$i + 1] - $slot : PHP_INT_MAX);
        }

        // The halfway points grow by at least a slot from one point to the
        // next, so the moments stay a slot apart.
        $previous = null;
        foreach ($order as $i => [$key, $at, [$from, $until]]) {
            $highest      = $this->highest($window, $points[$key], $visibility[$key]['transits'], $from, $until);
            $halfway      = $at + intdiv($latest[$i] - $at, 2);
            $order[$i][1] = max($at, $previous === null ? $at : $previous + $slot, min($highest, $halfway));
            $previous     = $order[$i][1];
        }

        return $order;
    }

    /**
     * @return array{order: list<array{0: int, 1: int, 2: array{0: int, 1: int}}>, missed: list<int>}
     */
    private function order(NightWindow $window, array $visibility, array $points, string $mode): array
    {
        $remaining = array_keys(array_filter($visibility, fn (array $v) => $v['intervals'] !== []));
        $missed    = array_keys(array_filter($visibility, fn (array $v) => $v['intervals'] === []));
        $slot      = $this->minutes * 60;
        $order     = [];
        $t         = $window->start;

        while ($remaining !== []) {
            $best = null;
            $next = null;
            foreach ($remaining as $position => $key) {
                foreach ($visibility[$key]['intervals'] as $interval) {
                    if ($interval[0] > $t) {
                        $next = min($next ?? $interval[0], $interval[0]);

                        continue;
                    }
                    if ($interval[1] < $t) {
                        continue;
                    }
                    $rank = match ($mode) {
                        'transit' => abs($t - $this->highest($window, $points[$key], $visibility[$key]['transits'], $interval[0], $interval[1])),
                        'soft'    => array_reduce($visibility[$key]['transits'], fn (int $rank, int $transit) => $transit >= $interval[0] && $transit <= $interval[1] ? min($rank, $transit + self::SOFT_HOURS * 3600) : $rank, $interval[1]),
                        default   => $interval[1],
                    };
                    $candidate = [$rank, $interval[1], $key];
                    if ($best === null || $candidate < $best['rank']) {
                        $best = ['rank' => $candidate, 'position' => $position, 'key' => $key, 'interval' => $interval];
                    }
                }
            }

            if ($best !== null) {
                $order[] = [$best['key'], $t, $best['interval']];
                unset($remaining[$best['position']]);
                $t += $slot;
            } elseif ($next !== null) {
                $t = $next;
            } else {
                break;
            }
        }

        return ['order' => $order, 'missed' => array_values(array_merge($missed, $remaining))];
    }
}
