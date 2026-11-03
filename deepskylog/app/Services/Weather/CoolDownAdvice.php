<?php

namespace App\Services\Weather;

use App\Models\Instrument;
use Carbon\Carbon;

/**
 * When to put a telescope with a mirror outside, so the mirror has cooled
 * to the air when the observing starts, from the temperatures of the
 * forecast (NightForecast::night()'s 'temperatures' and 'start').
 *
 * The mirror follows the air with a time constant (Newton's law of cooling,
 * dT/dt = (air − mirror) / τ): τ grows with the thickness of the mirror,
 * about TAU_MINUTES_PER_MM per millimetre in an open tube without a fan
 * (an inch thick mirror takes about an hour to come within a degree, after
 * Greer, Sky & Telescope 2000), FAN_FACTOR shorter with a fan behind it and
 * CLOSED_TUBE_FACTOR longer in a closed tube (Schmidt-Cassegrain, Maksutov).
 * The thickness is not stored: thickness() takes 1:6 up to 200 mm and
 * thinner beyond, as most mirrors of large Dobsonians are. The mirror starts
 * at INDOOR_C; it is cooled when within TOLERANCE_C of the air. While the
 * air keeps cooling the mirror stays behind (by the cooling rate times τ):
 * 'lag' is the most it lags in the dark hours after the start.
 */
class CoolDownAdvice
{
    /** The instruments smaller than this (mm) cool fast enough. */
    public const MIN_APERTURE_MM = 100;

    /** The temperature (°C) of the room the telescope comes from. */
    public const INDOOR_C = 20.0;

    /** A mirror this close (°C) to the air no longer spoils the image. */
    public const TOLERANCE_C = 1.0;

    public const TAU_MINUTES_PER_MM = 1.1;

    public const FAN_FACTOR = 3.0;

    public const CLOSED_TUBE_FACTOR = 1.5;

    /** The instrument types with a closed tube, which cool more slowly and seldom have a fan. */
    public const CLOSED_TUBE_TYPES = ['Cassegrain', 'Maksutov', 'Schmidt Cassegrain'];

    /** Put-out times are rounded to this many minutes. */
    private const ROUND_MINUTES = 5;

    public static function applies(?Instrument $instrument): bool
    {
        return $instrument !== null && $instrument->hasMirrors() && (float) $instrument->aperture_mm >= self::MIN_APERTURE_MM;
    }

    /**
     * The estimated thickness (mm) of the mirror.
     */
    public static function thickness(float $apertureMm): float
    {
        return $apertureMm / (6 + max(0.0, $apertureMm - 200) / 100);
    }

    /**
     * The time constant (minutes) of the instrument's mirror.
     */
    public static function timeConstant(Instrument $instrument, bool $fan = false): float
    {
        $tau = self::TAU_MINUTES_PER_MM * self::thickness((float) $instrument->aperture_mm);
        if (self::closedTube($instrument)) {
            return $tau * self::CLOSED_TUBE_FACTOR;
        }

        return $fan ? $tau / self::FAN_FACTOR : $tau;
    }

    public static function closedTube(Instrument $instrument): bool
    {
        return in_array($instrument->instrument_type?->name, self::CLOSED_TUBE_TYPES, true);
    }

    /**
     * The advice for a night, or null when it does not apply or the forecast
     * has no temperatures: 'put_out' (H:i, null when the air is no colder
     * than the room), 'put_out_fan' (with a fan, null for a closed tube),
     * 'ready' (H:i, the start of the observing), 'minutes' before it,
     * 'in_time' and 'fan_in_time' (false when the air cools so fast that the
     * mirror is not within TOLERANCE_C at 'ready' even when put outside at
     * the start of the temperatures; 'put_out' is then that moment) and
     * 'behind' (°C, how much warmer than the air the mirror then is), the
     * air at the start and at the end of the dark hours ('air_start',
     * 'air_end', °C), 'lag' (°C, the most the mirror stays further than
     * TOLERANCE_C behind the cooling air without a fan), 'lag_fan' and
     * 'closed_tube'.
     *
     * @param  array<string, mixed>  $night  as NightForecast::night() gives it
     * @return array<string, mixed>|null
     */
    public static function forNight(array $night, ?Instrument $instrument): ?array
    {
        $points = $night['temperatures'] ?? [];
        if (! self::applies($instrument) || count($points) < 2 || ! isset($night['start'], $night['end'])) {
            return null;
        }

        $start  = (int) $night['start'];
        $end    = (int) $night['end'];
        $air    = fn (int $t) => self::interpolate($points, $t);
        $time   = fn (?int $t) => $t === null ? null : Carbon::createFromTimestamp($t, $night['timezone'] ?? 'UTC')->format('H:i');
        $closed = self::closedTube($instrument);

        $tau    = self::timeConstant($instrument);
        $putOut = self::putOut($air, $start, (int) $points[0][0], $tau);
        $fanTau = $closed ? null : self::timeConstant($instrument, true);
        $fan    = $fanTau === null ? null : self::putOut($air, $start, (int) $points[0][0], $fanTau);
        $behind = fn (?int $out, float $tau) => $out === null ? 0.0 : self::mirrorAt($air, $out, $start, self::INDOOR_C, $tau) - $air($start);

        return [
            'in_time'     => $behind($putOut, $tau)                  <= self::TOLERANCE_C,
            'fan_in_time' => $fan !== null && $behind($fan, $fanTau) <= self::TOLERANCE_C,
            'behind'      => round($behind($putOut, $tau), 1),
            'put_out'     => $time($putOut),
            'put_out_fan' => $putOut === null || $fan === null ? null : $time($fan),
            'ready'       => $time($start),
            'minutes'     => $putOut === null ? 0 : intdiv($start - $putOut, 60),
            'air_start'   => round($air($start), 1),
            'air_end'     => round($air($end), 1),
            'lag'         => round(self::lag($air, $start, $end, $tau), 1),
            'lag_fan'     => $fanTau === null ? null : round(self::lag($air, $start, $end, $fanTau), 1),
            'closed_tube' => $closed,
        ];
    }

    /**
     * The advice in words: when to put the instrument outside, how much the
     * air cools during the night and whether the mirror keeps up with it.
     *
     * @param  array<string, mixed>  $advice  as forNight() gives it
     */
    public static function label(array $advice, Instrument $instrument, ?string $locale = null): string
    {
        $name  = html_entity_decode($instrument->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $parts = [match (true) {
            ! ($advice['in_time'] ?? true) && ($advice['fan_in_time'] ?? false) => __('Put the :instrument outside as early as you can: without a fan the mirror is still :behind °C warmer than the air at :ready; with a fan behind the mirror, put it outside at :fan', ['instrument' => $name, 'behind' => $advice['behind'], 'ready' => $advice['ready'], 'fan' => $advice['put_out_fan']], $locale),
            ! ($advice['in_time'] ?? true)                                      => __('Put the :instrument outside as early as you can: the mirror is still :behind °C warmer than the air at :ready', ['instrument' => $name, 'behind' => $advice['behind'], 'ready' => $advice['ready']], $locale),
            $advice['put_out'] === null                                         => __(':instrument needs no cool-down: the air is :temperature °C at :ready', ['instrument' => $name, 'temperature' => $advice['air_start'], 'ready' => $advice['ready']], $locale),
            $advice['put_out_fan'] !== null                                     => __('Put the :instrument outside at :time (with a fan behind the mirror at :fan) to have it cooled at :ready', ['instrument' => $name, 'time' => $advice['put_out'], 'fan' => $advice['put_out_fan'], 'ready' => $advice['ready']], $locale),
            default                                                             => __('Put the :instrument outside at :time to have it cooled at :ready', ['instrument' => $name, 'time' => $advice['put_out'], 'ready' => $advice['ready']], $locale),
        }];
        $parts[] = __('Temperature :from → :until °C', ['from' => $advice['air_start'], 'until' => $advice['air_end']], $locale);
        if ($advice['lag'] > 0) {
            $parts[] = $advice['lag_fan'] !== null && $advice['lag_fan'] <= 0
                ? __('Without a fan the mirror stays :lag °C behind the cooling air', ['lag' => $advice['lag']], $locale)
                : __('The mirror stays :lag °C behind the cooling air', ['lag' => $advice['lag']], $locale);
        }

        return implode(' · ', $parts);
    }

    /**
     * The latest moment (a multiple of ROUND_MINUTES) the telescope can go
     * outside to be within TOLERANCE_C of the air at $start, at the earliest
     * $earliest; null when the air is not colder than the room.
     */
    private static function putOut(callable $air, int $start, int $earliest, float $tau): ?int
    {
        if ($air($start) >= self::INDOOR_C - self::TOLERANCE_C) {
            return null;
        }

        $step = self::ROUND_MINUTES   * 60;
        $t    = intdiv($start, $step) * $step;
        while ($t > $earliest && self::mirrorAt($air, $t, $start, self::INDOOR_C, $tau) - $air($start) > self::TOLERANCE_C) {
            $t -= $step;
        }

        return max($t, intdiv($earliest + $step - 1, $step) * $step);
    }

    /**
     * The most (°C) the mirror, cooled at $start, stays above the air until $end.
     */
    private static function lag(callable $air, int $start, int $end, float $tau): float
    {
        $mirror = $air($start) + self::TOLERANCE_C;
        $lag    = 0.0;
        for ($t = $start; $t < $end; $t += 60) {
            $mirror = self::mirrorAt($air, $t, $t + 60, $mirror, $tau);
            $lag    = max($lag, $mirror - $air($t + 60));
        }

        return max(0.0, $lag - self::TOLERANCE_C);
    }

    /**
     * The temperature of the mirror at $to that was $mirror at $from, in steps of a minute.
     */
    private static function mirrorAt(callable $air, int $from, int $to, float $mirror, float $tau): float
    {
        for ($t = $from; $t < $to; $t += 60) {
            $mirror += ($air($t) - $mirror) / $tau;
        }

        return $mirror;
    }

    /**
     * The temperature at $t, linear between the points, constant beyond them.
     *
     * @param  list<array{0: int, 1: float}>  $points
     */
    private static function interpolate(array $points, int $t): float
    {
        if ($t <= $points[0][0]) {
            return $points[0][1];
        }
        foreach ($points as $i => $point) {
            if ($t <= $point[0]) {
                [$t0, $v0] = $points[$i - 1];

                return $v0 + ($point[1] - $v0) * ($t - $t0) / ($point[0] - $t0);
            }
        }

        return $points[count($points) - 1][1];
    }
}
