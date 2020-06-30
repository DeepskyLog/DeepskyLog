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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

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
    public function target(): HasOne
    {
        return $this->hasOne('App\Target', 'id', 'target_id');
    }

    /**
     * Get catalogs from the TargetName.
     *
     * @return Collection The list with the different catalogs
     */
    public static function getCatalogs(): Collection
    {
        // First get the deepsky catalogs
        $catalogs = TargetName::where('catalog', '!=', '')
            ->select('catalog')->distinct()->get()->pluck('catalog');

        // We add the comets, planets, Moon, Moon craters, ..., Sun.
        $catalogs->push(_i('Planets'));
        $catalogs->push(_i('Planetary Moons'));
        $catalogs->push(_i('Moon Craters'));
        $catalogs->push(_i('Moon Mountains'));
        $catalogs->push(_i('Moon Other Feature'));
        $catalogs->push(_i('Moon Sea'));
        $catalogs->push(_i('Moon Valley'));
        $catalogs->push(_i('Sun'));
        $catalogs->push(_i('Comets'));
        $catalogs->push(_i('Asteroids'));
        $catalogs->push(_i('Dwarf Planets'));

        return $catalogs->sort();
    }

    /**
     * Check if the object has alternative names.
     *
     * @param Target $target the target
     *
     * @return bool True if the object has alternative names
     */
    public static function hasAlternativeNames(Target $target): bool
    {
        if (self::where('target_id', $target->id)->get()->count() > 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the alternative names of the object.
     *
     * @param Target $target the target
     *
     * @return string The alternative names of the object
     */
    public static function getAlternativeNames(Target $target): string
    {
        $alternativeNames = '';
        foreach (self::where('target_id', $target->id)->get() as $targetname) {
            if ($targetname->altname != $target->target_name) {
                $alternativeNames .= ($alternativeNames ? '/' : '')
                    . $targetname->altname;
            }
        }

        return $alternativeNames;
    }
}
