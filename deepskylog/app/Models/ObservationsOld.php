<?php

/**
 * Old observations eloquent model.
 */

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
