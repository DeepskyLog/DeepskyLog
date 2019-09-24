<?php

 /**
  * TargetType eloquent model.
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
  * TargetType eloquent model.
  *
  * @category Targets
  * @package  DeepskyLog
  * @author   Wim De Meester <deepskywim@gmail.com>
  * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
  * @link     http://www.deepskylog.org
  */
class TargetType extends Model
{
    /**
     * Adds the link to the targets.
     *
     * @return BelongsTo the targets this type belongs to
     */
    public function targets()
    {
        return $this->belongsTo('App\Target', 'type', 'type');
    }

    /**
     * Target types have exactly one observation type.
     *
     * @return HasOne The eloquent relationship
     */
    public function observation_type()
    {
        return $this->hasOne('App\observationTypes', 'type', 'observation_type');
    }
}
