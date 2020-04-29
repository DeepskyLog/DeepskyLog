<?php

/**
 * TargetType eloquent model.
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
 * TargetType eloquent model.
 *
 * @category Targets
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TargetType extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Adds the link to the targets.
     *
     * @return BelongsTo the targets this type belongs to
     */
    public function target()
    {
        return $this->belongsTo('App\Target', 'id', 'type');
    }

    /**
     * Target types have exactly one observation type.
     *
     * @return HasOne The eloquent relationship
     */
    public function observationType()
    {
        return $this->hasOne('App\ObservationType', 'type', 'observation_type');
    }
}
