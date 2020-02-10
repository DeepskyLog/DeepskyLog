<?php

/**
 * TargetPartOf eloquent model.
 *
 * PHP Version 7
 *
 * @category Targets
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * TargetPartOf eloquent model.
 *
 * @category Targets
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TargetPartOf extends Model
{
    protected $table = 'target_partof';

    /**
     * Check if the object is part of another object.
     *
     * @param string $name the name of the object
     *
     * @return bool True if the object is part of another object
     */
    public static function isPartOf($name)
    {
        return \App\TargetPartOf::where('objectname', $name)->get()->count();
    }

    /**
     * Check if the object contains other objects.
     *
     * @param string $name the name of the object
     *
     * @return bool True if the object contains other objects
     */
    public static function contains($name)
    {
        return \App\TargetPartOf::where('partofname', $name)->get()->count();
    }

    /**
     * Returns the string with the information if the object contains or is
     * part of another object.
     *
     * @param string $name the name of the object
     *
     * @return string The string with the contains / part of information
     */
    public static function partOfContains($name)
    {
        $output = '(';

        $contains = '';
        if (\App\TargetPartOf::contains($name)) {
            foreach (\App\TargetPartOf::where('partofname', $name)->get() as $partOfObject) {
                $contains .= ($contains ? "/" : "")
                    . '<a href="/target/' . $partOfObject->objectname . '">'
                    . $partOfObject->objectname . "</a>";
            }
        } else {
            $contains .= '-';
        }
        $output .= $contains;

        $output .= ')/';

        $partOf = '';
        if (\App\TargetPartOf::isPartOf($name)) {
            foreach (\App\TargetPartOf::where('objectname', $name)->get() as $partOfObject) {
                $partOf .= ($partOf ? "/" : "")
                    . '<a href="/target/' . $partOfObject->partofname . '">'
                    . $partOfObject->partofname . "</a>";
            }
        } else {
            $partOf .= '-';
        }
        $output .= $partOf;

        return $output;
    }
}