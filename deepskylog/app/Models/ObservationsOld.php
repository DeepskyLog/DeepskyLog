<?php

/**
 * Old observations eloquent model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Old observers eloquent model.
 */
class ObservationsOld extends Model
{
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

        return ObservationsOld::where('date', '>=', $lastYear)->where('hasDrawing', 1)->count();
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
}
