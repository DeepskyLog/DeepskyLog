<?php

namespace App\Models;

use App\Services\Observations\ObservationCardData;
use App\Traits\ClearsResponseCache;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Magnitude;
use App\Traits\DeferredPagination;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Observation extends Model
{
    use ClearsResponseCache;
    use DeferredPagination;

    protected $table = 'observations';

    protected $fillable = [
        'legacy_source',
        'legacy_id',
        'observer_username',
        'observer_user_id',
        'instrument_id',
        'location_id',
        'eyepiece_id',
        'filter_id',
        'lens_id',
        'observed_on',
        'observed_time',
        'legacy_date_int',
        'legacy_time_int',
        'legacy_timestamp',
        'description',
        'language',
        'has_drawing',
        'drawing_filename',
        'target_type',
        'target_table',
        'target_key',
        'target_name',
        'seeing',
        'transparency',
        'limiting_magnitude',
        'sqm',
    ];

    /**
     * The statistics on an observer's page are cached for six hours
     * (ComputeObserverStats); forget them when one of their observations is
     * saved or deleted, so the page shows the change at once. When the
     * observer of an observation changes, both observers are cleared.
     */
    protected static function booted(): void
    {
        static::saved(fn (self $observation) => $observation->forgetObserverStats());
        static::deleted(fn (self $observation) => $observation->forgetObserverStats());
        // A new, moved or deleted observation changes the index in the detail URLs.
        static::saved(fn () => ObservationCardData::instance()->forgetSameDay());
        static::deleted(fn () => ObservationCardData::instance()->forgetSameDay());
    }

    public function forgetObserverStats(): void
    {
        $userIds = array_filter([$this->observer_user_id, $this->getOriginal('observer_user_id')]);
        $usernames = array_filter([$this->observer_username, $this->getOriginal('observer_username')]);
        if ($usernames !== []) {
            $userIds = array_merge($userIds, User::whereIn('username', $usernames)->pluck('id')->all());
        }

        foreach (array_unique($userIds) as $userId) {
            Cache::forget('observer_stats:'.$userId);
        }
    }

    protected function casts(): array
    {
        return [
            'observed_on' => 'date',
            'observed_time' => 'string',
            'seeing' => 'integer',
            'transparency' => 'integer',
            'has_drawing' => 'boolean',
        ];
    }

    /**
     * Store observed_on as Y-m-d, as the DATE column does in MySQL. The date
     * cast alone writes Y-m-d H:i:s, which SQLite keeps, so an exact
     * where('observed_on', 'Y-m-d') (which, unlike whereDate(), can use the
     * (observer_username, observed_on) index) would not match there.
     */
    protected function observedOn(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value === null || $value === '' ? null : Carbon::parse($value)->format('Y-m-d'),
        );
    }

    // ─── Target of an observation ──────────────────────────────────────────────
    //
    // What was observed is given by target_type, target_table and target_key,
    // the same for observations migrated from the old DeepskyLog and for
    // observations made in this version. legacy_source and legacy_id only record
    // where a migrated row came from; never use them to decide what an
    // observation is of.
    //
    //   deepsky  objects         target_key = objects.name (the canonical name, not an alias)
    //   comet    cometobjects    target_key = cometobjects.id (the id does not change when a comet is renamed)
    //   planet   planets         target_key = slug
    //   lunar    moons           target_key = slug
    //   lunar    lunar_features  target_key = slug
    //
    // target_name is the name shown to the user; for deep-sky objects it equals
    // target_key.

    public const TYPE_DEEPSKY = 'deepsky';

    public const TYPE_COMET = 'comet';

    /**
     * deepsky_observation_details.visibility of an object that was definitely not seen.
     */
    public const VISIBILITY_NOT_SEEN = 7;

    /**
     * The target an observation counts for when counting different objects.
     */
    public const DIFFERENT_OBJECT_SQL = "COALESCE(NULLIF(TRIM(observations.target_key), ''), LOWER(TRIM(observations.target_name)))";

    /**
     * The number of different objects among the observations of the query, the
     * one definition used on /observers, /statistics and the observer pages:
     * one per target, not counting observations of objects that were not seen.
     */
    public static function countDifferentObjects(EloquentBuilder|QueryBuilder $query): int
    {
        $query = ($query instanceof EloquentBuilder ? $query->toBase() : $query)->clone();

        return (int) $query
            ->whereNotExists(function (QueryBuilder $details) {
                $details->selectRaw('1')
                    ->from('deepsky_observation_details')
                    ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
                    ->where('deepsky_observation_details.visibility', self::VISIBILITY_NOT_SEEN);
            })
            ->selectRaw('COUNT(DISTINCT '.self::DIFFERENT_OBJECT_SQL.') as aggregate')
            ->value('aggregate');
    }

    public function scopeDeepsky($query)
    {
        return $query->where('observations.target_type', self::TYPE_DEEPSKY);
    }

    public function scopeComets($query)
    {
        return $query->where('observations.target_type', self::TYPE_COMET);
    }

    /**
     * Observations of one deep-sky object, given by its canonical name (objects.name).
     */
    public function scopeOfDeepskyObject($query, string $objectName)
    {
        return $query->where('observations.target_type', self::TYPE_DEEPSKY)
            ->where('observations.target_key', $objectName);
    }

    /**
     * Observations of one comet, given by its id (cometobjects.id).
     */
    public function scopeOfComet($query, int|string $cometId)
    {
        return $query->where('observations.target_type', self::TYPE_COMET)
            ->where('observations.target_key', (string) $cometId);
    }

    /**
     * The canonical name (objects.name) of a deep-sky object given by its name,
     * one of its aliases or its slug; null when there is no such object.
     */
    public static function canonicalDeepskyName(?string $nameOrSlug): ?string
    {
        $value = trim((string) $nameOrSlug);
        if ($value === '') {
            return null;
        }

        $name = DB::table('objects')->where('name', $value)->value('name')
            ?? DB::table('objects')->where('slug', $value)->value('name')
            ?? DB::table('objectnames')->where('altname', $value)->value('objectname')
            ?? DB::table('objectnames')->where('slug', $value)->value('objectname');

        return $name !== null ? (string) $name : null;
    }

    // ─── Detail page URL ────────────────────────────────────────────────────────
    //
    // /observation/{observer}/{date}/{object}/{index?}: the index numbers the
    // observations of one observer of one object on one day, ordered by time
    // and id, and is left out for the first. ObservationsController::detail()
    // resolves it the same way.

    /**
     * Variants of an object name or slug used to match a URL segment
     * tolerantly ("M 31", "m-31", "m31", "M 031").
     *
     * @return array<int, string>
     */
    public static function slugCandidates(string $value): array
    {
        $lower = mb_strtolower(trim($value));
        $slug = Str::slug($value, '-');
        $compact = str_replace([' ', '-'], '', $lower);
        $noLeadingZeros = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower) ?? $lower;

        return array_values(array_unique(array_filter([
            $lower,
            $slug,
            $compact,
            $noLeadingZeros,
            str_replace('-', '', $slug),
        ])));
    }

    public static function slugMatches(string $routeValue, string $name): bool
    {
        if ($name === '') {
            return false;
        }

        return array_intersect(static::slugCandidates($routeValue), static::slugCandidates($name)) !== [];
    }

    /**
     * The position (from 1) of this observation among those of the same
     * observer of the same object on the same day.
     */
    public function detailIndex(): int
    {
        if (!$this->observed_on) {
            return 1;
        }

        $objectSlug = Str::slug((string) $this->target_name, '-');
        $position = ObservationCardData::instance()
            ->sameDay((string) $this->observer_username, $this->observed_on->format('Y-m-d'))
            ->filter(fn($candidate) => static::slugMatches($objectSlug, (string) $candidate->target_name))
            ->values()
            ->search(fn($candidate) => (int) $candidate->id === (int) $this->id);

        return $position === false ? 1 : $position + 1;
    }

    /**
     * URL of the detail page, or null without a date or object.
     *
     * @param  string|null  $observer  the observer segment (slug), if already known
     */
    public function detailUrl(?string $observer = null): ?string
    {
        $objectSlug = Str::slug((string) $this->target_name, '-');
        if (!$this->observed_on || $objectSlug === '') {
            return null;
        }

        $observer ??= ObservationCardData::instance()->user($this->observer_username)?->slug ?: $this->observer_username;
        $params = [
            'observer' => $observer,
            'date' => $this->observed_on->format('Y-m-d'),
            'object' => $objectSlug,
        ];
        $index = $this->detailIndex();
        if ($index > 1) {
            $params['index'] = $index;
        }

        return route('observations.detail', $params);
    }

    // ─── Legacy field aliases (backward compatibility) ─────────────────────────

    public function getObserveridAttribute(): ?string
    {
        return $this->observer_username;
    }

    public function getObjectnameAttribute(): ?string
    {
        return $this->target_name;
    }

    public function getDateAttribute(): ?string
    {
        // Return the legacy YYYYMMDD integer format as a string for backward compat
        if ($this->legacy_date_int !== null) {
            return (string) $this->legacy_date_int;
        }
        if ($this->observed_on !== null) {
            return Carbon::parse($this->observed_on)->format('Ymd');
        }

        return null;
    }

    /**
     * Legacy-compatible UT time as an HHMM integer (2130 = 21:30), or null when
     * the time is unknown. Migrated observations without a time have
     * legacy_time_int = -9999 and were given observed_time 23:59:00.
     */
    public function getTimeAttribute(): ?int
    {
        $legacy = $this->attributes['legacy_time_int'] ?? null;
        $observed = $this->attributes['observed_time'] ?? null;

        if ($observed !== null && $observed !== '') {
            if ($legacy !== null && (int) $legacy < 0 && str_starts_with((string) $observed, '23:59')) {
                return null;
            }
            if (preg_match('/^(\d{1,2}):(\d{2})/', (string) $observed, $m)) {
                $hours = (int) $m[1];
                $minutes = (int) $m[2];
                if ($hours <= 23 && $minutes <= 59) {
                    return $hours * 100 + $minutes;
                }
            }
        }

        if ($legacy !== null && (int) $legacy >= 0 && (int) $legacy <= 2359 && (int) $legacy % 100 < 60) {
            return (int) $legacy;
        }

        return null;
    }

    /** Magnification read from the detail tables; false = not read yet. */
    private string|null|false $storedMagnification = false;

    /**
     * Magnification stored with the observation (in deepsky_observation_details
     * or comet_observation_details), or null when none was given. A value set
     * on the model (e.g. by a join) is used as it is.
     */
    public function getMagnificationAttribute(): ?string
    {
        if (array_key_exists('magnification', $this->attributes)) {
            return $this->attributes['magnification'];
        }
        if ($this->storedMagnification !== false) {
            return $this->storedMagnification;
        }

        $table = match ($this->attributes['target_type'] ?? null) {
            self::TYPE_DEEPSKY => 'deepsky_observation_details',
            self::TYPE_COMET => 'comet_observation_details',
            default => null,
        };
        $value = $table !== null && $this->exists
            ? DB::table($table)->where('observation_id', $this->getKey())->value('magnification')
            : null;

        return $this->storedMagnification = self::storedMagnificationValue($value);
    }

    /**
     * Read the magnifications of these observations from the detail tables in
     * one query per table, so the magnification accessor does not query per
     * observation (the observation cards show it).
     *
     * @param  iterable<Observation>  $observations
     */
    public static function preloadMagnifications(iterable $observations): void
    {
        $byTable = [];
        foreach ($observations as $observation) {
            if (! $observation->exists || $observation->storedMagnification !== false
                || array_key_exists('magnification', $observation->attributes)) {
                continue;
            }
            $table = match ($observation->attributes['target_type'] ?? null) {
                self::TYPE_DEEPSKY => 'deepsky_observation_details',
                self::TYPE_COMET => 'comet_observation_details',
                default => null,
            };
            if ($table === null) {
                $observation->storedMagnification = null;
            } else {
                $byTable[$table][$observation->getKey()] = $observation;
            }
        }

        foreach ($byTable as $table => $models) {
            $values = DB::table($table)->whereIn('observation_id', array_keys($models))->pluck('magnification', 'observation_id');
            foreach ($models as $id => $observation) {
                $observation->storedMagnification = self::storedMagnificationValue($values->get($id));
            }
        }
    }

    private static function storedMagnificationValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' && (float) $value > 0 ? $value : null;
    }

    /**
     * Normalise a magnification entered by the user ("120", "120x", "120,5")
     * to the value stored in the detail tables, or null when there is none.
     */
    public static function normalizeMagnification(mixed $value): ?string
    {
        $value = str_replace(',', '.', trim((string) $value));
        $value = preg_replace('/\s*[xX×]$/u', '', $value) ?? $value;
        if (!is_numeric($value) || (float) $value <= 0) {
            return null;
        }

        return (string) (float) round((float) $value, 1);
    }

    // ─── Date and time ─────────────────────────────────────────────────────────
    //
    // observed_on and observed_time are stored in UT (as in the old DeepskyLog).
    // The user enters and sees them in the time zone of the observing location.

    /**
     * Time zone of the observing location; UTC when the observation has none.
     */
    public function locationTimezone(?Location $location = null): string
    {
        $timezone = $location?->timezone
            ?? ($this->location_id ? Location::whereKey($this->location_id)->value('timezone') : null);

        return self::validTimezone($timezone);
    }

    /**
     * Moment of the observation in the time zone of the location, or null when
     * the time is unknown.
     */
    public function localObservedAt(?Location $location = null): ?Carbon
    {
        $time = $this->time;
        if ($time === null || $this->observed_on === null) {
            return null;
        }

        return Carbon::parse($this->observed_on)->setTimezone('UTC')
            ->setTime(intdiv($time, 100), $time % 100)
            ->setTimezone($this->locationTimezone($location));
    }

    /**
     * Local date (Y-m-d) of the observation. Without a time the stored date is
     * the only one there is.
     */
    public function localDate(?Location $location = null): ?string
    {
        $local = $this->localObservedAt($location);
        if ($local !== null) {
            return $local->format('Y-m-d');
        }

        return $this->observed_on !== null ? Carbon::parse($this->observed_on)->format('Y-m-d') : null;
    }

    /**
     * Local time (H:i) of the observation, or null when unknown.
     */
    public function localTime(?Location $location = null): ?string
    {
        return $this->localObservedAt($location)?->format('H:i');
    }

    /**
     * Convert a date and time entered in the time zone of the location to the
     * UT date (Y-m-d) and time (H:i:s) that are stored. Without a time the date
     * is kept as it is.
     *
     * @return array{0: string, 1: ?string}
     */
    public static function utcFromLocal(string $date, ?string $time, ?string $timezone): array
    {
        $date = trim($date);
        $time = trim((string) $time);
        if ($time === '') {
            return [$date, null];
        }

        try {
            $utc = Carbon::parse(substr($date, 0, 10) . ' ' . $time, self::validTimezone($timezone))->setTimezone('UTC');

            return [$utc->format('Y-m-d'), $utc->format('H:i:s')];
        } catch (\Throwable $_) {
            return [$date, $time];
        }
    }

    private static function validTimezone(?string $timezone): string
    {
        if (!empty($timezone) && in_array($timezone, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC), true)) {
            return $timezone;
        }

        return 'UTC';
    }

    public function getHasDrawingAttribute(): bool
    {
        return (bool) ($this->attributes['has_drawing'] ?? false);
    }

    public function getInstrumentidAttribute(): ?int
    {
        $v = $this->attributes['instrument_id'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    public function getLocationidAttribute(): ?int
    {
        $v = $this->attributes['location_id'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    public function getEyepieceidAttribute(): ?int
    {
        $v = $this->attributes['eyepiece_id'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    public function getFilteridAttribute(): ?int
    {
        $v = $this->attributes['filter_id'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    public function getLensidAttribute(): ?int
    {
        $v = $this->attributes['lens_id'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    public function getObjectidAttribute(): ?string
    {
        return $this->attributes['target_key'] ?? null;
    }

    // ────────────────────────────────────────────────────────────────────────────

    public static function getObservationsCountByTargetTableKey(string $targetTable, string $targetKey): int
    {
        if (empty($targetTable) || empty($targetKey)) {
            return 0;
        }

        return static::where('target_table', $targetTable)
            ->where('target_key', $targetKey)
            ->count();
    }

    public static function getDrawingsCountByTargetTableKey(string $targetTable, string $targetKey): int
    {
        if (empty($targetTable) || empty($targetKey)) {
            return 0;
        }

        return static::where('target_table', $targetTable)
            ->where('target_key', $targetKey)
            ->where('has_drawing', 1)
            ->count();
    }

    public static function getObservationsCountForUserByTargetKey(?\App\Models\User $user = null, ?string $targetTable = null, ?string $targetKey = null): int
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return 0;
        }

        $query = static::where('observer_user_id', $user->id);
        if ($targetTable) {
            $query->where('target_table', $targetTable);
        }
        if ($targetKey) {
            $query->where('target_key', $targetKey);
        }

        return $query->count();
    }

    public static function getDrawingsCountForUserByTargetKey(?\App\Models\User $user = null, ?string $targetTable = null, ?string $targetKey = null): int
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return 0;
        }

        $query = static::where('observer_user_id', $user->id)->where('has_drawing', 1);
        if ($targetTable) {
            $query->where('target_table', $targetTable);
        }
        if ($targetKey) {
            $query->where('target_key', $targetKey);
        }

        return $query->count();
    }

    /**
     * Backwards-compatible accessor for legacy templates that expect `SQM`.
     * Returns stored `sqm` or computes it from `limiting_magnitude` when possible.
     */
    public function getSQMAttribute()
    {
        if (array_key_exists('sqm', $this->attributes) && $this->attributes['sqm'] !== null) {
            return (float) $this->attributes['sqm'];
        }

        if (array_key_exists('limiting_magnitude', $this->attributes) && $this->attributes['limiting_magnitude'] !== null) {
            try {
                return round(Magnitude::nelmToSqm((float) $this->attributes['limiting_magnitude']), 2);
            } catch (\Throwable $_) {
                return null;
            }
        }

        return null;
    }

    /**
     * Backwards-compatible accessor for legacy templates that expect `limmag`.
     * Returns stored `limiting_magnitude` or computes it from `sqm` when possible.
     */
    public function getLimmagAttribute()
    {
        if (array_key_exists('limiting_magnitude', $this->attributes) && $this->attributes['limiting_magnitude'] !== null) {
            return (float) $this->attributes['limiting_magnitude'];
        }

        if (array_key_exists('sqm', $this->attributes) && $this->attributes['sqm'] !== null) {
            try {
                return round(Magnitude::sqmToNelm((float) $this->attributes['sqm']), 1);
            } catch (\Throwable $_) {
                return null;
            }
        }

        return null;
    }

    /**
     * Compute a Bortle value when possible from SQM or limiting magnitude.
     */
    public function getBortleAttribute()
    {
        if (array_key_exists('sqm', $this->attributes) && $this->attributes['sqm'] !== null) {
            try {
                return Magnitude::sqmToBortle((float) $this->attributes['sqm']);
            } catch (\Throwable $_) {
                // fall through
            }
        }

        if (array_key_exists('limiting_magnitude', $this->attributes) && $this->attributes['limiting_magnitude'] !== null) {
            try {
                return Magnitude::nelmToBortle((float) $this->attributes['limiting_magnitude']);
            } catch (\Throwable $_) {
                // fall through
            }
        }

        return null;
    }

    // ---------------------------------------------------------------------------
    // Backward-compatible static helpers replacing ObservationsOld / CometObservationsOld
    // ---------------------------------------------------------------------------

    // Aliases used by views (matching ObservationsOld method signatures exactly)
    public static function getObservationsCountForObject(string $objectName): int
    {
        return static::getObservationsCountForTargetName($objectName);
    }

    public static function getDrawingsCountForObject(string $objectName): int
    {
        return static::getDrawingsCountForTargetName($objectName);
    }

    public static function getObservationsCountForUser(?\App\Models\User $user = null, ?string $objectName = null): int
    {
        return static::getObservationsCountForUserByName($user, $objectName);
    }

    public static function getDrawingsCountForUser(?\App\Models\User $user = null, ?string $objectName = null): int
    {
        return static::getDrawingsCountForUserByName($user, $objectName);
    }

    public static function getTotalObservations(?string $source = null): int
    {
        $q = static::query();
        if ($source !== null) {
            $q->where('target_type', $source);
        }

        return $q->count();
    }

    public static function getTotalObservationsLastYear(?string $source = null): int
    {
        $lastYear = now()->subYear();
        $q = static::where('observed_on', '>=', $lastYear->format('Y-m-d'));
        if ($source !== null) {
            $q->where('target_type', $source);
        }

        return $q->count();
    }

    public static function getTotalDrawingsLastYear(?string $source = null): int
    {
        $lastYear = now()->subYear();
        $q = static::where('observed_on', '>=', $lastYear->format('Y-m-d'))->where('has_drawing', 1);
        if ($source !== null) {
            $q->where('target_type', $source);
        }

        return $q->count();
    }

    public static function getUniqueObjectsObserved(?string $source = null): int
    {
        $q = static::query();
        if ($source !== null) {
            $q->where('target_type', $source);
        }

        return $q->distinct('target_name')->count('target_name');
    }

    /**
     * Count observations for an object identified by target_name (legacy usage).
     */
    public static function getObservationsCountForTargetName(string $targetName, ?string $source = null): int
    {
        if ($targetName === '') {
            return 0;
        }
        $q = static::where('target_name', $targetName);
        if ($source !== null) {
            $q->where('target_type', $source);
        }

        return $q->count();
    }

    /**
     * Count drawings for an object identified by target_name (legacy usage).
     */
    public static function getDrawingsCountForTargetName(string $targetName, ?string $source = null): int
    {
        if ($targetName === '') {
            return 0;
        }
        $q = static::where('target_name', $targetName)->where('has_drawing', 1);
        if ($source !== null) {
            $q->where('target_type', $source);
        }

        return $q->count();
    }

    /**
     * Count observations for a user, optionally filtered by target_name (legacy usage).
     */
    public static function getObservationsCountForUserByName(?\App\Models\User $user = null, ?string $targetName = null, ?string $source = null): int
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return 0;
        }
        $q = static::where('observer_user_id', $user->id);
        if ($targetName !== null && $targetName !== '') {
            $q->where('target_name', $targetName);
        }
        if ($source !== null) {
            $q->where('target_type', $source);
        }

        return $q->count();
    }

    /**
     * Count drawings for a user, optionally filtered by target_name (legacy usage).
     */
    public static function getDrawingsCountForUserByName(?\App\Models\User $user = null, ?string $targetName = null, ?string $source = null): int
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return 0;
        }
        $q = static::where('observer_user_id', $user->id)->where('has_drawing', 1);
        if ($targetName !== null && $targetName !== '') {
            $q->where('target_name', $targetName);
        }
        if ($source !== null) {
            $q->where('target_type', $source);
        }

        return $q->count();
    }

    /**
     * Get the date of the last observation for a user, optionally filtered by target_name.
     */
    public static function getLastObservationDateForUser(?\App\Models\User $user = null, ?string $targetName = null): ?Carbon
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return null;
        }
        $q = static::where('observer_user_id', $user->id);
        if ($targetName !== null && $targetName !== '') {
            $q->where('target_name', $targetName);
        }
        $date = $q->orderByDesc('observed_on')->value('observed_on');

        return $date ? Carbon::parse($date) : null;
    }

    /**
     * Get the date of the last drawing for a user, optionally filtered by target_name.
     */
    public static function getLastDrawingDateForUser(?\App\Models\User $user = null, ?string $targetName = null): ?Carbon
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return null;
        }
        $q = static::where('observer_user_id', $user->id)->where('has_drawing', 1);
        if ($targetName !== null && $targetName !== '') {
            $q->where('target_name', $targetName);
        }
        $date = $q->orderByDesc('observed_on')->value('observed_on');

        return $date ? Carbon::parse($date) : null;
    }

    /**
     * Return a mapping eyepiece_id => array of instrument_ids for the given eyepiece IDs.
     *
     * @param  int[]  $eyepieceIds
     */
    public static function getInstrumentsForEyepieceIds(array $eyepieceIds): array
    {
        if (empty($eyepieceIds)) {
            return [];
        }
        $rows = static::whereIn('eyepiece_id', $eyepieceIds)
            ->whereNotNull('instrument_id')
            ->groupBy('eyepiece_id', 'instrument_id')
            ->get(['eyepiece_id', 'instrument_id']);

        $map = [];
        foreach ($rows as $r) {
            $eid = (int) $r->eyepiece_id;
            $iid = (int) $r->instrument_id;
            if (!isset($map[$eid])) {
                $map[$eid] = [];
            }
            $map[$eid][] = $iid;
        }

        return $map;
    }

    /**
     * Return a mapping eyepiece_id => ['date' => <formatted string>, 'id' => Observation]
     * for the first (earliest) observation for each eyepiece id, the same
     * observation that Eyepiece::first_observation_date() gives without the map.
     *
     * @param  int[]  $eyepieceIds
     */
    public static function getFirstObservationDateAndIdForEyepieceIds(array $eyepieceIds): array
    {
        return static::observationDateMapForEyepieceIds($eyepieceIds, false);
    }

    /**
     * Return a mapping eyepiece_id => ['date' => <formatted string>, 'id' => Observation]
     * for the last (latest) observation for each eyepiece id.
     *
     * @param  int[]  $eyepieceIds
     */
    public static function getLastObservationDateAndIdForEyepieceIds(array $eyepieceIds): array
    {
        return static::observationDateMapForEyepieceIds($eyepieceIds, true);
    }

    /**
     * On the first or last date, the observation with the lowest id, as in
     * HasObservationsDates::get_date_and_id_generic().
     *
     * @param  int[]  $eyepieceIds
     */
    private static function observationDateMapForEyepieceIds(array $eyepieceIds, bool $last): array
    {
        if (empty($eyepieceIds)) {
            return [];
        }
        $rows = static::whereIn('eyepiece_id', $eyepieceIds)
            ->whereNotNull('observed_on')
            ->orderBy('eyepiece_id')
            ->orderBy('observed_on', $last ? 'desc' : 'asc')
            ->orderBy('id')
            ->get();

        $language = app()->getLocale();
        $map = [];
        foreach ($rows as $r) {
            $eid = (int) $r->eyepiece_id;
            if (isset($map[$eid])) {
                continue;
            }
            $formatted = Carbon::parse($r->observed_on)->locale($language)->isoFormat('LL');
            $map[$eid] = ['date' => $formatted, 'id' => $r];
        }

        return $map;
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class, 'instrument_id');
    }

    public function eyepiece(): BelongsTo
    {
        return $this->belongsTo(Eyepiece::class, 'eyepiece_id');
    }

    public function observerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observer_user_id');
    }
}
