<?php

namespace App\Services\Observations;

use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\SuspectObservation;
use App\Models\User;
use App\Services\ContrastReserveCalculator;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Finds the observations that look wrong and keeps them in
 * suspect_observations (SuspectObservation):
 *
 * - below_horizon: the object stood more than TargetAltitude::TOLERANCE
 *   below the horizon of the location at the moment (UT) of the observation,
 *   or, without a time, never rises there (the import refuses these unless
 *   asked, but the old site and the form did not check them);
 * - too_faint: a deep-sky object marked as seen (not of
 *   FAINT_EXCLUDED_TYPES) whose contrast reserve with the instrument is
 *   below FAINT_BELOW, under the sky of the observation
 *   (its SQM or limiting magnitude, else the location's) and dimmed by the
 *   extinction at its altitude then, at the best of the magnification
 *   recorded and the observer's magnifications with that instrument, so only
 *   observations no magnification explains are listed;
 * - duplicate: an observation of the same observer, object, date, time,
 *   instrument, eyepiece, filter and lens as one with a lower id
 *   (related_observation_id), mostly an observation imported twice.
 *
 * check() is too slow to run in a request for many observations (the
 * contrast reserve, a comet's position): observations:check-suspect runs it
 * hourly on the observations changed since the last run and weekly on all.
 * A finding that is dismissed stays dismissed while the check finds it.
 */
class SuspectObservations
{
    /**
     * A contrast reserve below this is too faint to have been seen. Clark's
     * model is pessimistic for experienced observers: at -0.5 a third of the
     * findings were galaxies seen by observers with large telescopes.
     */
    public const FAINT_BELOW = -1.0;

    /**
     * Types whose visibility the contrast reserve does not describe: groups
     * of stars or galaxies seen as separate points, dark nebulae, objects
     * that do not exist. Double stars have none (ContrastReserveCalculator::appliesTo()).
     */
    public const FAINT_EXCLUDED_TYPES = ['ASTER', 'OPNCL', 'GALCL', 'DRKNB', 'NONEX'];

    /** The observations read and checked at once. */
    public const CHUNK = 500;

    /** The columns that decide the findings: changing one forgets them. */
    public const CHECKED_COLUMNS = [
        'observer_username', 'observed_on', 'observed_time', 'location_id', 'instrument_id', 'eyepiece_id',
        'filter_id', 'lens_id', 'target_table', 'target_key', 'sqm', 'limiting_magnitude',
    ];

    /** @var array<string, array<int, list<Eyepiece>>> magnifications per instrument and observer */
    private array $magnifications = [];

    /** @var array<string, ?User> */
    private array $users = [];

    /**
     * Check the observations for below_horizon and too_faint, replacing
     * their earlier findings. Returns the number checked and found per
     * reason.
     *
     * @param  Builder<Observation>  $observations
     * @param  callable(int): void|null  $progress  called with the number checked after each chunk
     * @return array{checked: int, below_horizon: int, too_faint: int}
     */
    public function check(Builder $observations, ?callable $progress = null): array
    {
        $totals = ['checked' => 0, SuspectObservation::REASON_BELOW_HORIZON => 0, SuspectObservation::REASON_TOO_FAINT => 0];

        $observations->chunkById(self::CHUNK, function ($chunk) use (&$totals, $progress) {
            $found = $this->checkChunk($chunk->all());
            $this->store($chunk->pluck('id')->all(), $found, [SuspectObservation::REASON_BELOW_HORIZON, SuspectObservation::REASON_TOO_FAINT]);

            $totals['checked'] += $chunk->count();
            foreach ($found as $reasons) {
                foreach (array_keys($reasons) as $reason) {
                    $totals[$reason]++;
                }
            }
            if ($progress !== null) {
                $progress($totals['checked']);
            }
        });

        return $totals;
    }

    /**
     * Find the duplicates among the observations of the observers (all
     * observers when null), replacing their earlier duplicate findings.
     * Returns the number of duplicates.
     *
     * @param  list<string>|null  $usernames
     */
    public function checkDuplicates(?array $usernames = null): int
    {
        $key = ['observer_username', 'target_table', 'target_key', 'observed_on', 'observed_time', 'instrument_id', 'eyepiece_id', 'filter_id', 'lens_id'];

        // The observers with a duplicate, in one grouped query
        $withDuplicates = DB::table('observations')
            ->when($usernames !== null, fn ($q) => $q->whereIn('observer_username', $usernames))
            ->groupBy($key)
            ->havingRaw('count(*) > 1')
            ->distinct()
            ->pluck('observer_username')
            ->all();
        $withDuplicates = array_values(array_unique($withDuplicates));

        $found = [];
        foreach ($withDuplicates as $username) {
            $first = [];
            foreach (DB::table('observations')->where('observer_username', $username)->orderBy('id')->get(array_merge(['id'], $key)) as $row) {
                $signature = json_encode(array_map(fn ($column) => $row->{$column} === null ? null : (string) $row->{$column}, $key));
                if (isset($first[$signature])) {
                    $found[(int) $row->id][SuspectObservation::REASON_DUPLICATE] = [[], $first[$signature]];
                } else {
                    $first[$signature] = (int) $row->id;
                }
            }
        }

        // Earlier findings of these observers that no longer hold are removed
        $earlier = SuspectObservation::query()
            ->where('reason', SuspectObservation::REASON_DUPLICATE)
            ->when($usernames !== null, fn ($q) => $q->whereIn('observation_id', Observation::query()->select('id')->whereIn('observer_username', $usernames)))
            ->pluck('observation_id')
            ->all();
        $this->store(array_values(array_unique(array_merge($earlier, array_keys($found)))), $found, [SuspectObservation::REASON_DUPLICATE]);

        return count($found);
    }

    /**
     * Forget the findings of an observation that changed (open ones only: a
     * dismissal stays until the next check) and the duplicate findings of
     * the observations that were a duplicate of it.
     */
    public static function forget(Observation $observation, bool $deleted = false): void
    {
        if (! $deleted) {
            SuspectObservation::query()->where('observation_id', $observation->id)->whereNull('dismissed_at')->delete();
        }
        SuspectObservation::query()->where('related_observation_id', $observation->id)->delete();
    }

    /** The number of open findings of the observer. */
    public static function openCount(User $user): int
    {
        return SuspectObservation::query()->open()->ofObserver($user)->distinct()->count('observation_id');
    }

    /**
     * The findings of a chunk of observations.
     *
     * @param  list<Observation>  $observations
     * @return array<int, array<string, array{0: array<string, mixed>, 1: ?int}>> observation id => reason => [details, related id]
     */
    private function checkChunk(array $observations): array
    {
        $locations = Location::query()->whereIn('id', collect($observations)->pluck('location_id')->filter()->unique())->get()->keyBy('id');
        $deepsky = collect($observations)->where('target_table', 'objects');
        $objects = DB::table('objects')->whereIn('name', $deepsky->pluck('target_key')->unique())
            ->get(['name', 'ra', 'decl', 'mag', 'diam1', 'diam2', 'type'])->keyBy(fn ($o) => mb_strtolower($o->name));
        $details = DB::table('deepsky_observation_details')->whereIn('observation_id', $deepsky->pluck('id'))
            ->get(['observation_id', 'visibility', 'magnification'])->keyBy('observation_id');
        $instruments = Instrument::query()->whereIn('id', $deepsky->pluck('instrument_id')->filter()->unique())->get()->keyBy('id');
        $eyepieces = Eyepiece::query()->whereIn('id', $deepsky->pluck('eyepiece_id')->filter()->unique())->get()->keyBy('id');
        $lenses = Lens::query()->whereIn('id', $deepsky->pluck('lens_id')->filter()->unique())->get()->keyBy('id');

        $found = [];
        foreach ($observations as $observation) {
            $location = $locations[$observation->location_id] ?? null;
            if (! TargetAltitude::usable($location)) {
                continue;
            }
            $time = $observation->time;
            $moment = $time !== null && $observation->observed_on !== null
                ? Carbon::parse($observation->observed_on->format('Y-m-d'), 'UTC')->setTime(intdiv($time, 100), $time % 100)
                : null;

            $object = $observation->target_table === 'objects' ? ($objects[mb_strtolower((string) $observation->target_key)] ?? null) : null;
            try {
                $altitude = $this->altitude($observation, $object, $location, $moment);
            } catch (\Throwable $e) {
                report($e);
                $altitude = null;
            }

            if ($altitude !== null && $altitude < -TargetAltitude::TOLERANCE) {
                $found[$observation->id][SuspectObservation::REASON_BELOW_HORIZON] = [[
                    'altitude' => round($altitude, 1),
                    'moment' => $moment?->format('Y-m-d H:i'),
                    'location' => html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                ], null];

                continue;
            }

            if ($object === null || ! ContrastReserveCalculator::appliesTo($object->type)
                || in_array(strtoupper(trim((string) $object->type)), self::FAINT_EXCLUDED_TYPES, true)) {
                continue;
            }
            $detail = $details[$observation->id] ?? null;
            if ($detail !== null && (int) $detail->visibility === Observation::VISIBILITY_NOT_SEEN) {
                continue;
            }
            $instrument = $instruments[$observation->instrument_id] ?? null;
            if ($instrument === null) {
                continue;
            }
            $faint = $this->faint($observation, $object, $instrument, $location, $altitude, $detail?->magnification,
                $eyepieces[$observation->eyepiece_id] ?? null, $lenses[$observation->lens_id] ?? null);
            if ($faint !== null) {
                $found[$observation->id][SuspectObservation::REASON_TOO_FAINT] = [$faint, null];
            }
        }

        return $found;
    }

    /**
     * The altitude of the target at the moment, or its highest altitude on
     * the date without a moment; null when unknown.
     */
    private function altitude(Observation $observation, ?object $object, Location $location, ?Carbon $moment): ?float
    {
        if ($observation->target_table === 'objects') {
            if ($object === null || ! is_numeric($object->ra) || ! is_numeric($object->decl)) {
                return null;
            }

            return TargetAltitude::ofCoordinates(new EquatorialCoordinates((float) $object->ra, (float) $object->decl), $location, $moment);
        }

        $target = [
            'type' => (string) $observation->target_type,
            'table' => (string) $observation->target_table,
            'key' => (string) $observation->target_key,
            'name' => (string) $observation->target_name,
        ];

        return TargetAltitude::forObservation($target, $location, $observation->observed_on->format('Y-m-d'), $moment?->format('H:i:s'));
    }

    /**
     * The details of a too_faint finding, or null when the object could be
     * seen (or its contrast reserve cannot be calculated).
     *
     * @return array<string, mixed>|null
     */
    private function faint(Observation $observation, object $object, Instrument $instrument, Location $location, ?float $altitude, mixed $storedMagnification, ?Eyepiece $eyepiece, ?Lens $lens): ?array
    {
        $sqm = $observation->sqm ?? $location->getSqm();
        if (! is_numeric($sqm) || (float) $sqm < 10 || (float) $instrument->aperture_mm <= 0) {
            return null;
        }

        $magnifications = $this->magnificationsOf($instrument, $observation->observer_username);
        $recorded = is_numeric($storedMagnification) && (float) $storedMagnification > 0
            ? (int) round((float) $storedMagnification)
            : $instrument->magnificationFor($eyepiece, $lens);
        if ($recorded !== null && $recorded > 0) {
            $magnifications[$recorded] ??= [];
        }

        // At the moment of the observation, else at the highest altitude
        if ($altitude === null || $observation->time === null) {
            $altitude = ContrastReserveCalculator::highestAltitude($object->decl, $location);
        }
        $target = ContrastReserveCalculator::target($object->diam1, $object->diam2, $object->mag);
        $result = ContrastReserveCalculator::calculate($target, $instrument, $location, null, 1.0, $magnifications, (float) $sqm, $altitude);
        $reserve = $result['contrast_reserve'];
        if ($reserve === null || $reserve >= self::FAINT_BELOW) {
            return null;
        }

        return [
            'contrast_reserve' => round($reserve, 2),
            'magnification' => $result['magnification'],
            'sqm' => round((float) $sqm, 2),
            'aperture' => (float) $instrument->aperture_mm,
            'altitude' => $altitude !== null ? round($altitude, 1) : null,
        ];
    }

    /**
     * The observer's magnifications with the instrument (their eyepieces,
     * else the generic ones).
     *
     * @return array<int, list<Eyepiece>>
     */
    private function magnificationsOf(Instrument $instrument, string $username): array
    {
        $key = $instrument->id.'|'.$username;
        if (! array_key_exists($key, $this->magnifications)) {
            if (! array_key_exists($username, $this->users)) {
                $this->users[$username] = User::query()->where('username', $username)->first();
            }
            $this->magnifications[$key] = ContrastReserveCalculator::magnifications($instrument, $this->users[$username]);
        }

        return $this->magnifications[$key];
    }

    /**
     * Store the findings of the observations for the reasons: new ones are
     * added, existing ones get their new details (keeping their dismissal),
     * and those no longer found are removed.
     *
     * @param  list<int>  $observationIds
     * @param  array<int, array<string, array{0: array<string, mixed>, 1: ?int}>>  $found
     * @param  list<string>  $reasons
     */
    private function store(array $observationIds, array $found, array $reasons): void
    {
        foreach (array_chunk($observationIds, self::CHUNK) as $ids) {
            $existing = SuspectObservation::query()->whereIn('observation_id', $ids)->whereIn('reason', $reasons)->get()
                ->keyBy(fn (SuspectObservation $s) => $s->observation_id.'|'.$s->reason);

            $remove = [];
            foreach ($existing as $key => $suspect) {
                if (! isset($found[$suspect->observation_id][$suspect->reason])) {
                    $remove[] = $suspect->id;
                }
            }
            if ($remove !== []) {
                SuspectObservation::query()->whereIn('id', $remove)->delete();
            }

            $now = now();
            foreach ($ids as $id) {
                foreach ($found[$id] ?? [] as $reason => [$details, $related]) {
                    $suspect = $existing[$id.'|'.$reason] ?? null;
                    if ($suspect === null) {
                        SuspectObservation::query()->insert([
                            'observation_id' => $id,
                            'reason' => $reason,
                            'details' => json_encode($details),
                            'related_observation_id' => $related,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    } elseif ($suspect->details != $details || $suspect->related_observation_id !== $related) {
                        $suspect->forceFill(['details' => $details, 'related_observation_id' => $related])->save();
                    }
                }
            }
        }
    }
}
