<?php

namespace App\Models;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Magnitude;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Observation extends Model
{
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
     * Return a mapping eyepiece_id => ['date' => <formatted string>, 'id' => <signed int>]
     * for the first (earliest) observation for each eyepiece id.
     *
     * @param  int[]  $eyepieceIds
     */
    public static function getFirstObservationDateAndIdForEyepieceIds(array $eyepieceIds): array
    {
        if (empty($eyepieceIds)) {
            return [];
        }
        $rows = static::whereIn('eyepiece_id', $eyepieceIds)
            ->orderBy('eyepiece_id')
            ->orderBy('observed_on')
            ->get(['eyepiece_id', 'observed_on', 'legacy_id', 'legacy_source']);

        $language = app()->getLocale();
        $map = [];
        foreach ($rows as $r) {
            $eid = (int) $r->eyepiece_id;
            if (isset($map[$eid])) {
                continue;
            }
            $formatted = Carbon::parse($r->observed_on)->locale($language)->isoFormat('LL');
            $id = ($r->legacy_source === 'comet') ? -(int) $r->legacy_id : (int) $r->legacy_id;
            $map[$eid] = ['date' => $formatted, 'id' => $id];
        }

        return $map;
    }

    /**
     * Return a mapping eyepiece_id => ['date' => <formatted string>, 'id' => <signed int>]
     * for the last (latest) observation for each eyepiece id.
     *
     * @param  int[]  $eyepieceIds
     */
    public static function getLastObservationDateAndIdForEyepieceIds(array $eyepieceIds): array
    {
        if (empty($eyepieceIds)) {
            return [];
        }
        $rows = static::whereIn('eyepiece_id', $eyepieceIds)
            ->orderBy('eyepiece_id')
            ->orderByDesc('observed_on')
            ->get(['eyepiece_id', 'observed_on', 'legacy_id', 'legacy_source']);

        $language = app()->getLocale();
        $map = [];
        foreach ($rows as $r) {
            $eid = (int) $r->eyepiece_id;
            if (isset($map[$eid])) {
                continue;
            }
            $formatted = Carbon::parse($r->observed_on)->locale($language)->isoFormat('LL');
            $id = ($r->legacy_source === 'comet') ? -(int) $r->legacy_id : (int) $r->legacy_id;
            $map[$eid] = ['date' => $formatted, 'id' => $id];
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
