<?php

/**
 * Target name eloquent model.
 *
 * PHP Version 7
 *
 * @category Targets
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Target name eloquent model.
 *
 * @category Targets
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TargetName extends Model
{
    protected $fillable = ['objectname', 'catalog', 'catindex', 'altname'];

    protected $primaryKey = 'altname';

    public $incrementing = false;

    /**
     * TargetNamess have exactly one Target.
     *
     * @return HasOne The eloquent relationship
     */
    public function target()
    {
        return $this->hasOne('App\Target', 'name', 'objectname');
    }

    /**
     * Get catalogs from the TargetName.
     *
     * @return Collection The list with the different catalogs
     */
    public static function getCatalogs()
    {
        /*
        // First get the deepsky catalogs
        $catalogs = TargetName::where('catalog', '!=', '')
            ->select('catalog')->distinct()->get();

        // We add the comets, planets, Moon, Moon craters, ..., Sun, ...
        $catalogs->push('{"catalog":"Solar System"}');
        dd($catalogs);

        return $catalogs;*/
        return self::where('catalog', '!=', '')
            ->select('catalog')->distinct()->get();
    }

    /**
     * Check if the object has alternative names.
     *
     * @param string $name the name of the object
     *
     * @return bool True if the object has alternative names
     */
    public static function hasAlternativeNames($name)
    {
        if (self::where('objectname', $name)->get()->count() > 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the alternative names of the object.
     *
     * @param string $name the name of the object
     *
     * @return string The alternative names of the object
     */
    public static function getAlternativeNames($name)
    {
        $alternativeNames = '';
        foreach (self::where('objectname', $name)->get() as $targetname) {
            if ($targetname->altname != $name) {
                $alternativeNames .= ($alternativeNames ? '/' : '')
                    .$targetname->altname;
            }
        }

        return $alternativeNames;
    }
}
