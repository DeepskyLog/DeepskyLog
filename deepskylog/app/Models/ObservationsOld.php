<?php

/**
 * Old observations eloquent model.
 */

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Old observers eloquent model.
 */
class ObservationsOld extends Model
{
    use ClearsResponseCache;

    protected $connection = 'mysqlOld';

    protected $table = 'observations';

    /**
     * Get the total number of observations.
     *
     * This static method returns the total count of observations
     * stored in the 'observations' table of the 'mysqlOld' database.
     * It uses Laravel's Eloquent ORM to execute the count query.
     *
     * @return int The total number of observations.
     */
    public static function getTotalObservations(): int
    {
        return ObservationsOld::count();
    }

    /**
     * Retrieves the total number of observations made in the last year.
     *
     * This method calculates the total number of observations made in the last year.
     * It first retrieves the current year and subtracts one to get the previous year.
     * It then queries the 'observations' table for entries where the 'date' column's year is greater than or equal to the last year.
     *
     * The count of these entries is returned, representing the total number of observations made in the last year.
     *
     * @return int The total number of observations made in the last year.
     */
    public static function getTotalObservationsLastYear(): int
    {
        // Get the observations from the last year
        $lastYear = date('Ymd') - 10000;

        return ObservationsOld::where('date', '>=', $lastYear)->count();
    }

    /**
     * Retrieves the total number of drawings made in the last year.
     *
     * This method calculates the total number of drawings made in the last year.
     * It first retrieves the current year and subtracts one to get the previous year.
     * It then queries the 'observations' table for entries where the 'date' column's year is greater than or equal to the last year
     * and the 'hasDrawing' column is set to 1 (indicating that the observation has a drawing).
     *
     * The count of these entries is returned, representing the total number of drawings made in the last year.
     *
     * @return int The total number of drawings made in the last year.
     */
    public static function getTotalDrawingsLastYear(): int
    {
        // Get the drawings from the last year
        $lastYear = date('Ymd') - 10000;

        return ObservationsOld::where('hasDrawing', 1)->where('date', '>=', $lastYear)->count();
    }

    /**
     * Retrieves the count of unique objects observed.
     *
     * This static method calculates the total number of unique objects observed.
     * It queries the 'observations' table for distinct entries in the 'objectname' column and counts them.
     *
     * The count of these entries is returned, representing the total number of unique objects observed.
     *
     * @return int The total number of unique objects observed.
     */
    public static function getUniqueObjectsObserved(): int
    {
        return ObservationsOld::distinct('objectname')->count('objectname');
    }

    /**
     * Establishes a relationship between the ObservationsOld model and the SketchOfTheWeek model.
     *
     * This method sets the database connection to 'mysql' and defines a one-to-one relationship between the ObservationsOld model and the SketchOfTheWeek model.
     * The relationship is established based on the 'id' attribute of the ObservationsOld model and the 'observation_id' attribute of the SketchOfTheWeek model.
     *
     * @return BelongsTo The relationship between the ObservationsOld model and the SketchOfTheWeek model.
     */
    public function sketchOfTheWeek(): BelongsTo
    {
        return $this->setConnection('mysql')->belongsTo(SketchOfTheWeek::class, 'id', 'observation_id');
    }

    /**
     * Establishes a relationship between the ObservationsOld model and the SketchOfTheMonth model.
     *
     * This method sets the database connection to 'mysql' and defines a one-to-one relationship between the ObservationsOld model and the SketchOfTheMonth model.
     * The relationship is established based on the 'id' attribute of the ObservationsOld model and the 'observation_id' attribute of the SketchOfTheMonth model.
     *
     * @return BelongsTo The relationship between the ObservationsOld model and the SketchOfTheMonth model.
     */
    public function sketchOfTheMonth(): BelongsTo
    {
        return $this->setConnection('mysql')->belongsTo(SketchOfTheMonth::class, 'id', 'observation_id');
    }

    /**
     * Relation to the User model using the legacy observer username stored in observerid.
     * This allows eager-loading the owning user by matching ObservationsOld.observerid -> users.username.
     */
    public function user(): BelongsTo
    {
        $relation = $this->belongsTo(\App\Models\User::class, 'observerid', 'username');

        // Ensure the related User model queries the default connection (not mysqlOld)
        $related = $relation->getRelated();
        $related->setConnection(config('database.default'));

        return $relation;
    }

    /**
     * Count observations for a specific object name (as stored in `objectname`).
     *
     * @param string $objectName
     * @return int
     */
    public static function getObservationsCountForObject(string $objectName): int
    {
        if ($objectName === '') {
            return 0;
        }

        return ObservationsOld::where('objectname', $objectName)->count();
    }

    /**
     * Count drawings (observations with hasDrawing=1) for a specific object.
     *
     * @param string $objectName
     * @return int
     */
    public static function getDrawingsCountForObject(string $objectName): int
    {
        if ($objectName === '') {
            return 0;
        }

        return ObservationsOld::where('objectname', $objectName)->where('hasDrawing', 1)->count();
    }

    /**
     * Count observations for a specific user (or the currently authenticated user)
     * optionally filtered to a specific object name (as stored in `objectname`).
     * The legacy table stores the username in `observerid`, so this method
     * will use the user's `username` property.
     *
     * @param \App\Models\User|null $user
     * @param string|null $objectName
     * @return int
     */
    public static function getObservationsCountForUser(?\App\Models\User $user = null, ?string $objectName = null): int
    {
        $user = $user ?? Auth::user();

        if (! $user || empty($user->username)) {
            return 0;
        }

        $query = ObservationsOld::where('observerid', $user->username);

        if ($objectName !== null && $objectName !== '') {
            $query->where('objectname', $objectName);
        }

        return $query->count();
    }

    /**
     * Count drawings (hasDrawing = 1) for a specific user (or the currently authenticated user)
     * optionally filtered to a specific object name.
     *
     * @param \App\Models\User|null $user
     * @param string|null $objectName
     * @return int
     */
    public static function getDrawingsCountForUser(?\App\Models\User $user = null, ?string $objectName = null): int
    {
        $user = $user ?? Auth::user();

        if (! $user || empty($user->username)) {
            return 0;
        }

        $query = ObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1);

        if ($objectName !== null && $objectName !== '') {
            $query->where('objectname', $objectName);
        }

        return $query->count();
    }

    /**
     * Get the date of the last observation for a specific user or the currently
     * authenticated user. Returns a Carbon instance or null when no observation exists.
     *
     * Note: the legacy `date` column stores the date as YYYYMMDD (integer), so
     * we create a Carbon instance from that format.
     *
     * @param \App\Models\User|null $user
     * @param string|null $objectName
     * @return Carbon|null
     */
    public static function getLastObservationDateForUser(?\App\Models\User $user = null, ?string $objectName = null): ?Carbon
    {
        $user = $user ?? Auth::user();

        if (! $user || empty($user->username)) {
            return null;
        }

        $query = ObservationsOld::where('observerid', $user->username);
        if ($objectName !== null && $objectName !== '') {
            $query->where('objectname', $objectName);
        }

        $lastDate = $query->orderByDesc('date')->value('date');

        if (! $lastDate) {
            return null;
        }

        $lastDateStr = (string) $lastDate;

        try {
            return Carbon::createFromFormat('Ymd', $lastDateStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the date of the last drawing for a specific user or the currently
     * authenticated user. Returns a Carbon instance or null when no drawing exists.
     *
     * @param \App\Models\User|null $user
     * @param string|null $objectName
     * @return Carbon|null
     */
    public static function getLastDrawingDateForUser(?\App\Models\User $user = null, ?string $objectName = null): ?Carbon
    {
        $user = $user ?? Auth::user();

        if (! $user || empty($user->username)) {
            return null;
        }

        $query = ObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1);
        if ($objectName !== null && $objectName !== '') {
            $query->where('objectname', $objectName);
        }

        $lastDate = $query->orderByDesc('date')->value('date');

        if (! $lastDate) {
            return null;
        }

        $lastDateStr = (string) $lastDate;

        try {
            return Carbon::createFromFormat('Ymd', $lastDateStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper to safely parse a legacy Ymd integer/string date into Carbon or null.
     *
     * @param mixed $date
     * @return Carbon|null
     */
    protected static function safeParseDate($date): ?Carbon
    {
        if (! $date) return null;
        $s = (string) $date;
        try {
            return Carbon::createFromFormat('Ymd', $s);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Return a mapping eyepiece_id => [instrumentid, ...] for the provided eyepiece ids.
     * Uses a grouped query to fetch all instrumentids for multiple eyepieces in one call.
     * Returns an associative array where keys are eyepiece ids (int) and values are arrays of instrument ids (int[]).
     */
    public static function getInstrumentsForEyepieceIds(array $eyepieceIds): array
    {
        if (empty($eyepieceIds)) return [];
        $rows = \DB::connection('mysqlOld')->table((new self())->getTable())
            ->select('eyepieceid', 'instrumentid')
            ->whereIn('eyepieceid', $eyepieceIds)
            ->whereNotNull('instrumentid')
            ->groupBy('eyepieceid', 'instrumentid')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $eid = (int) $r->eyepieceid;
            $iid = (int) $r->instrumentid;
            if (! isset($map[$eid])) $map[$eid] = [];
            $map[$eid][] = $iid;
        }
        return $map;
    }

    /**
     * Return a mapping eyepiece_id => ['date' => int, 'id' => int] for the first (earliest)
     * observation for each eyepiece id in the provided array. The method performs a
     * single query ordered by eyepieceid,date asc and picks the first row per eyepiece.
     *
     * @param int[] $eyepieceIds
     * @return array
     */
    public static function getFirstObservationDateAndIdForEyepieceIds(array $eyepieceIds): array
    {
        if (empty($eyepieceIds)) return [];

        $rows = \DB::connection('mysqlOld')->table((new self())->getTable())
            ->select('eyepieceid', 'date', 'id')
            ->whereIn('eyepieceid', $eyepieceIds)
            ->orderBy('eyepieceid')
            ->orderBy('date')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $eid = (int) $r->eyepieceid;
            if (! isset($map[$eid])) {
                $map[$eid] = ['date' => (int) $r->date, 'id' => (int) $r->id];
            }
        }

        return $map;
    }

    /**
     * Return a mapping eyepiece_id => ['date' => int, 'id' => int] for the last (latest)
     * observation for each eyepiece id in the provided array.
     *
     * @param int[] $eyepieceIds
     * @return array
     */
    public static function getLastObservationDateAndIdForEyepieceIds(array $eyepieceIds): array
    {
        if (empty($eyepieceIds)) return [];

        $rows = \DB::connection('mysqlOld')->table((new self())->getTable())
            ->select('eyepieceid', 'date', 'id')
            ->whereIn('eyepieceid', $eyepieceIds)
            ->orderBy('eyepieceid')
            ->orderBy('date', 'desc')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $eid = (int) $r->eyepieceid;
            if (! isset($map[$eid])) {
                $map[$eid] = ['date' => (int) $r->date, 'id' => (int) $r->id];
            }
        }

        return $map;
    }
}
