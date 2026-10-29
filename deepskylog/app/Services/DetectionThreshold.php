<?php

namespace App\Services;

use App\Jobs\FitDetectionThreshold;
use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\SuspectObservations;
use App\Services\Observations\TargetAltitude;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * "Your own eye": the contrast reserve at which an observer sees an object
 * half the time, learned from their deep-sky observations. The contrast
 * reserve is that of a model observer (Clark), who sees an object from a
 * reserve of 0; an experienced observer sees fainter, a beginner less.
 *
 * Every deep-sky observation of a type with a contrast reserve (not of
 * SuspectObservations::FAINT_EXCLUDED_TYPES) gets the reserve the model
 * predicts for it (SuspectObservations::predictedReserve(): the sky of the
 * observation, the extinction at its moment, the best of the recorded and
 * the observer's magnifications). Seen counts 1, "Object definitely not seen"
 * 0 and "Perception of object is very questionable" ½, and fit() fits
 * P(seen) = 1 / (1 + e^-(a + b·reserve)) by maximum likelihood; the threshold
 * is -a/b. Observers mostly record what they saw, so it needs at least
 * MIN_NOT_SEEN observations that were not (or questionably) seen.
 *
 * The fit is stored on the user (users.detection_threshold and
 * detection_threshold_fit, with the signature() of the observations it was
 * made from), refitted by FitDetectionThreshold when the profile page finds
 * it stale and by observers:fit-detection-thresholds. With
 * users.use_detection_threshold, offsetFor() is added to the contrast
 * reserves of Tonight's best, Next for you and What's new for me.
 */
class DetectionThreshold
{
    /** The least number of observations not seen (very questionable ones count half). */
    public const MIN_NOT_SEEN = 10;

    /** The least number of observations seen. */
    public const MIN_SEEN = 30;

    /**
     * Predicted reserves outside this range are left out: mostly a size or
     * magnitude in the wrong unit, or another object than the one recorded.
     */
    public const RESERVE_RANGE = [-2.0, 2.5];

    /**
     * The slope b the fit is drawn towards (PRIOR_SLOPE_WEIGHT, a standard
     * deviation of about 3), so a few observations not seen, all below the
     * seen ones, give a threshold between them instead of an infinitely
     * steep curve. 3 is about what the observers with many observations not
     * seen get: from 18% to 82% seen over one unit of contrast reserve.
     */
    public const PRIOR_SLOPE = 3.0;

    private const PRIOR_SLOPE_WEIGHT = 0.1;

    /**
     * The weight drawing a towards 0, the threshold towards the model's:
     * with b = PRIOR_SLOPE a standard deviation of 0.5 in reserve.
     */
    private const PRIOR_INTERCEPT_WEIGHT = 0.44;

    /**
     * Below this slope, the chance of seeing an object hardly changes with
     * its contrast reserve, and the threshold means nothing.
     */
    public const MIN_SLOPE = 1.0;

    /** A threshold is used (offsetFor()) only with at most this standard error. */
    public const MAX_STANDARD_ERROR = 0.2;

    /** The largest offset applied, either way. */
    public const MAX_OFFSET = 0.5;

    /** The width of the bins of the chart on the profile page. */
    public const BIN_WIDTH = 0.25;

    /** The visibility counted as half seen. */
    public const VISIBILITY_QUESTIONABLE = 6;

    /** Part of the signature: raise it when the fit or its data changes. */
    private const VERSION = 1;

    /**
     * The fit stored for the observer, or null when there is none.
     *
     * @return array{threshold: ?float, slope: ?float, standard_error: ?float, reliable: bool, seen: int, not_seen: int, questionable: int, used: int, bins: list<array{from: float, to: float, seen: float, not_seen: float}>, signature: string, fitted_at: string, reason: ?string}|null
     */
    public static function stored(User $user): ?array
    {
        $fit = $user->detection_threshold_fit;

        return is_array($fit) ? $fit : null;
    }

    /**
     * The offset to add to the observer's contrast reserves: minus their
     * threshold, when they chose to use it and it is reliable; else 0.
     */
    public static function offsetFor(?User $user): float
    {
        if (! $user || ! $user->use_detection_threshold) {
            return 0.0;
        }
        $fit = self::stored($user);
        if (! $fit || ! ($fit['reliable'] ?? false) || ! is_numeric($fit['threshold'] ?? null)) {
            return 0.0;
        }

        return round(max(-self::MAX_OFFSET, min(self::MAX_OFFSET, -(float) $fit['threshold'])), 2);
    }

    /**
     * Changes with the observer's deep-sky observations and their details.
     */
    public static function signature(User $user): string
    {
        $row = DB::table('observations as o')
            ->leftJoin('deepsky_observation_details as d', 'd.observation_id', '=', 'o.id')
            ->where('o.observer_username', $user->username)
            ->where('o.target_table', 'objects')
            ->selectRaw('COUNT(*) as count, MAX(o.updated_at) as updated, SUM(d.visibility) as visibility, SUM(d.magnification) as magnification')
            ->first();

        return md5(json_encode([self::VERSION, (array) $row]));
    }

    /**
     * The number of the observer's deep-sky observations not seen or very
     * questionable, of every type: an upper bound of what fit() counts.
     */
    public static function notSeenCount(User $user): int
    {
        return DB::table('observations as o')
            ->join('deepsky_observation_details as d', 'd.observation_id', '=', 'o.id')
            ->where('o.observer_username', $user->username)
            ->where('o.target_table', 'objects')
            ->whereIn('d.visibility', [Observation::VISIBILITY_NOT_SEEN, self::VISIBILITY_QUESTIONABLE])
            ->count();
    }

    /**
     * The observer's deep-sky observations seen, not seen and very
     * questionable, of every type, in one query: what the profile page shows
     * when there are too few observations not seen to fit.
     *
     * @return array{seen: int, not_seen: int, questionable: int}
     */
    public static function counts(User $user): array
    {
        $row = DB::table('observations as o')
            ->leftJoin('deepsky_observation_details as d', 'd.observation_id', '=', 'o.id')
            ->where('o.observer_username', $user->username)
            ->where('o.target_table', 'objects')
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN d.visibility = ? THEN 1 ELSE 0 END) as not_seen, SUM(CASE WHEN d.visibility = ? THEN 1 ELSE 0 END) as questionable', [
                Observation::VISIBILITY_NOT_SEEN, self::VISIBILITY_QUESTIONABLE,
            ])
            ->first();
        $notSeen      = (int) ($row->not_seen ?? 0);
        $questionable = (int) ($row->questionable ?? 0);

        return ['seen' => (int) ($row->total ?? 0) - $notSeen - $questionable, 'not_seen' => $notSeen, 'questionable' => $questionable];
    }

    /**
     * The usernames of the observers with at least MIN_NOT_SEEN deep-sky
     * observations not seen or very questionable, in one grouped query.
     *
     * @return list<string>
     */
    public static function candidates(): array
    {
        return DB::table('observations as o')
            ->join('deepsky_observation_details as d', 'd.observation_id', '=', 'o.id')
            ->where('o.target_table', 'objects')
            ->whereIn('d.visibility', [Observation::VISIBILITY_NOT_SEEN, self::VISIBILITY_QUESTIONABLE])
            ->groupBy('o.observer_username')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_NOT_SEEN])
            ->pluck('o.observer_username')
            ->map(fn ($username) => (string) $username)
            ->all();
    }

    /** Whether the stored fit was made from other observations than the observer has now. */
    public static function stale(User $user): bool
    {
        return (self::stored($user)['signature'] ?? null) !== self::signature($user);
    }

    /**
     * Queues a refit; false when one is already waiting.
     */
    public static function queue(User $user): bool
    {
        if (! Cache::add(self::pendingKey($user), true, now()->addHour())) {
            return false;
        }
        FitDetectionThreshold::dispatch($user->id);

        return true;
    }

    public static function pending(User $user): bool
    {
        return Cache::has(self::pendingKey($user));
    }

    public static function forgetPending(User $user): void
    {
        Cache::forget(self::pendingKey($user));
    }

    private static function pendingKey(User $user): string
    {
        return 'detection_threshold:pending:'.$user->id;
    }

    /**
     * Fits the observer's threshold and stores it; returns the stored fit.
     */
    public static function compute(User $user): array
    {
        $signature = self::signature($user);
        if (self::notSeenCount($user) >= self::MIN_NOT_SEEN) {
            $points = self::points($user);
            $fit    = self::fit($points);
        } else {
            // without enough observations not seen, the reserves are not
            // worth calculating: the counts of all deep-sky observations
            $points = [];
            $fit    = [...self::fit([]), ...self::counts($user)];
        }
        $result = [
            ...$fit,
            'bins'      => self::bins($points),
            'signature' => $signature,
            'fitted_at' => now()->toIso8601String(),
        ];

        $user->forceFill([
            'detection_threshold'     => $fit['threshold'],
            'detection_threshold_fit' => $result,
        ])->saveQuietly();

        return $result;
    }

    /**
     * The observer's deep-sky observations as the predicted contrast reserve
     * and how much they were seen (1, ½ or 0), within RESERVE_RANGE.
     *
     * @return list<array{reserve: float, seen: float}>
     */
    public static function points(User $user): array
    {
        $suspect = new SuspectObservations();
        $points  = [];

        Observation::query()
            ->where('observer_username', $user->username)
            ->where('target_table', 'objects')
            ->whereNotNull('instrument_id')
            ->whereNotNull('location_id')
            ->chunkById(SuspectObservations::CHUNK, function ($chunk) use ($suspect, &$points) {
                $locations   = Location::query()->whereIn('id', $chunk->pluck('location_id')->unique())->get()->keyBy('id');
                $instruments = Instrument::query()->whereIn('id', $chunk->pluck('instrument_id')->unique())->get()->keyBy('id');
                $eyepieces   = Eyepiece::query()->whereIn('id', $chunk->pluck('eyepiece_id')->filter()->unique())->get()->keyBy('id');
                $lenses      = Lens::query()->whereIn('id', $chunk->pluck('lens_id')->filter()->unique())->get()->keyBy('id');
                $objects     = DB::table('objects')->whereIn('name', $chunk->pluck('target_key')->unique())
                    ->get(['name', 'ra', 'decl', 'mag', 'diam1', 'diam2', 'type'])->keyBy(fn ($o) => mb_strtolower($o->name));
                $details = DB::table('deepsky_observation_details')->whereIn('observation_id', $chunk->pluck('id'))
                    ->get(['observation_id', 'visibility', 'magnification'])->keyBy('observation_id');

                foreach ($chunk as $observation) {
                    $object     = $objects[mb_strtolower((string) $observation->target_key)] ?? null;
                    $location   = $locations[$observation->location_id]                      ?? null;
                    $instrument = $instruments[$observation->instrument_id]                  ?? null;
                    if ($object === null || $location === null || $instrument === null || ! TargetAltitude::usable($location)
                                         || ! ContrastReserveCalculator::appliesTo($object->type)
                                         || in_array(strtoupper(trim((string) $object->type)), SuspectObservations::FAINT_EXCLUDED_TYPES, true)) {
                        continue;
                    }
                    $detail = $details[$observation->id] ?? null;
                    $seen   = match ((int) ($detail?->visibility ?? 0)) {
                        Observation::VISIBILITY_NOT_SEEN => 0.0,
                        self::VISIBILITY_QUESTIONABLE    => 0.5,
                        default                          => 1.0,
                    };

                    $time   = $observation->time;
                    $moment = $time !== null && $observation->observed_on !== null
                        ? Carbon::parse($observation->observed_on->format('Y-m-d'), 'UTC')->setTime(intdiv($time, 100), $time % 100)
                        : null;
                    try {
                        $altitude = $suspect->altitude($observation, $object, $location, $moment);
                        if ($altitude !== null && $altitude < 0) {
                            continue;
                        }
                        $predicted = $suspect->predictedReserve(
                            $observation,
                            $object,
                            $instrument,
                            $location,
                            $altitude,
                            $detail?->magnification,
                            $eyepieces[$observation->eyepiece_id] ?? null,
                            $lenses[$observation->lens_id]        ?? null
                        );
                    } catch (\Throwable $e) {
                        report($e);

                        continue;
                    }
                    if ($predicted === null) {
                        continue;
                    }
                    $reserve = $predicted['contrast_reserve'];
                    if ($reserve < self::RESERVE_RANGE[0] || $reserve > self::RESERVE_RANGE[1]) {
                        continue;
                    }
                    $points[] = ['reserve' => $reserve, 'seen' => $seen];
                }
            });

        return $points;
    }

    /**
     * The penalized maximum likelihood fit of P(seen) = 1 / (1 + e^-(a + b·reserve))
     * (Newton's method; the penalties keep it finite when the observations
     * not seen all lie below the seen ones), with the threshold -a/b and its
     * standard error. reason is why there is no threshold, as a translation
     * key.
     *
     * @param  list<array{reserve: float, seen: float}>  $points
     * @return array{threshold: ?float, slope: ?float, standard_error: ?float, reliable: bool, seen: int, not_seen: int, questionable: int, used: int, reason: ?string}
     */
    public static function fit(array $points): array
    {
        $seen         = count(array_filter($points, fn (array $p) => $p['seen'] === 1.0));
        $notSeen      = count(array_filter($points, fn (array $p) => $p['seen'] === 0.0));
        $questionable = count($points) - $seen - $notSeen;
        $result       = [
            'threshold'      => null,
            'slope'          => null,
            'standard_error' => null,
            'reliable'       => false,
            'seen'           => $seen,
            'not_seen'       => $notSeen,
            'questionable'   => $questionable,
            'used'           => count($points),
            'reason'         => null,
        ];
        if ($notSeen + $questionable / 2 < self::MIN_NOT_SEEN || $seen < self::MIN_SEEN) {
            return [...$result, 'reason' => 'Not enough observations yet.'];
        }

        // Seen and not seen weigh the same in total, so the threshold does
        // not depend on how many of the objects not seen were recorded
        // (observers mostly record what they saw). Each in total as much as
        // the smaller of the two, so the standard error is that of the
        // observations not seen rather than of the many seen.
        $seenTotal     = array_sum(array_column($points, 'seen'));
        $notSeenTotal  = count($points) - $seenTotal;
        $smaller       = min($seenTotal, $notSeenTotal);
        $seenWeight    = $smaller / $seenTotal;
        $notSeenWeight = $smaller / $notSeenTotal;
        foreach ($points as &$p) {
            $p['w1'] = $p['seen']       * $seenWeight;
            $p['w0'] = (1 - $p['seen']) * $notSeenWeight;
        }
        unset($p);

        [$a, $b] = [0.0, self::PRIOR_SLOPE];
        $loss    = fn (float $a, float $b) => array_sum(array_map(function (array $p) use ($a, $b) {
            $z = $a + $b * $p['reserve'];

            // -log likelihood (log(1 + e^-z) seen, log(1 + e^z) not seen), finite for large |z|
            $softplus = log1p(exp(-abs($z))) + max($z, 0.0);

            return $p['w1'] * ($softplus - $z) + $p['w0'] * $softplus;
        }, $points)) + self::PRIOR_INTERCEPT_WEIGHT / 2 * $a ** 2 + self::PRIOR_SLOPE_WEIGHT / 2 * ($b - self::PRIOR_SLOPE) ** 2;

        $hessian = [[0.0, 0.0], [0.0, 0.0]];
        for ($iteration = 0; $iteration < 50; $iteration++) {
            $ga      = self::PRIOR_INTERCEPT_WEIGHT * $a;
            $gb      = self::PRIOR_SLOPE_WEIGHT     * ($b - self::PRIOR_SLOPE);
            $hessian = [[self::PRIOR_INTERCEPT_WEIGHT, 0.0], [0.0, self::PRIOR_SLOPE_WEIGHT]];
            foreach ($points as $p) {
                $probability = 1 / (1 + exp(-($a + $b * $p['reserve'])));
                $weight      = $p['w1'] + $p['w0'];
                $residual    = $weight * $probability - $p['w1'];
                $w           = $weight * $probability * (1 - $probability);
                $ga            += $residual;
                $gb            += $residual * $p['reserve'];
                $hessian[0][0] += $w;
                $hessian[0][1] += $w * $p['reserve'];
                $hessian[1][1] += $w * $p['reserve'] ** 2;
            }
            $hessian[1][0] = $hessian[0][1];
            $determinant   = $hessian[0][0] * $hessian[1][1] - $hessian[0][1] ** 2;
            if ($determinant <= 0) {
                break;
            }
            $da = ($hessian[1][1] * $ga - $hessian[0][1] * $gb) / $determinant;
            $db = ($hessian[0][0] * $gb - $hessian[0][1] * $ga) / $determinant;

            // halve the step until the loss goes down
            $before = $loss($a, $b);
            $step   = 1.0;
            while ($step > 1e-4 && $loss($a - $step * $da, $b - $step * $db) > $before) {
                $step /= 2;
            }
            $a -= $step * $da;
            $b -= $step * $db;
            if (abs($step * $da) < 1e-6 && abs($step * $db) < 1e-6) {
                break;
            }
        }

        if ($b < self::MIN_SLOPE) {
            return [...$result, 'reason' => 'Your observations do not show a threshold yet: objects with a lower contrast reserve were not clearly seen less often.'];
        }

        // the standard error of -a/b from the inverse Hessian (delta method)
        $determinant = $hessian[0][0] * $hessian[1][1] - $hessian[0][1] ** 2;
        $error       = null;
        if ($determinant > 0) {
            [$da, $db] = [-1 / $b, $a / $b ** 2];
            $variance  = ($hessian[1][1] * $da ** 2 - 2 * $hessian[0][1] * $da * $db + $hessian[0][0] * $db ** 2) / $determinant;
            $error     = $variance > 0 ? sqrt($variance) : null;
        }
        $threshold = -$a / $b;

        return [
            ...$result,
            'threshold'      => round($threshold, 3),
            'slope'          => round($b, 3),
            'standard_error' => $error !== null ? round($error, 3) : null,
            'reliable'       => $error !== null && $error <= self::MAX_STANDARD_ERROR,
        ];
    }

    /**
     * The points per BIN_WIDTH of contrast reserve: how many were seen and
     * not seen (a very questionable one half of each), for the chart of the
     * profile page.
     *
     * @param  list<array{reserve: float, seen: float}>  $points
     * @return list<array{from: float, to: float, seen: float, not_seen: float}>
     */
    public static function bins(array $points): array
    {
        $bins = [];
        foreach ($points as $p) {
            $index = (int) floor($p['reserve'] / self::BIN_WIDTH);
            $bins[$index] ??= ['from' => round($index * self::BIN_WIDTH, 2), 'to' => round(($index + 1) * self::BIN_WIDTH, 2), 'seen' => 0.0, 'not_seen' => 0.0];
            $bins[$index]['seen']     += $p['seen'];
            $bins[$index]['not_seen'] += 1 - $p['seen'];
        }
        ksort($bins);

        return array_values($bins);
    }
}
