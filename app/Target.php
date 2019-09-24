<?php

 /**
  * Target eloquent model.
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
  * Target eloquent model.
  *
  * @category Targets
  * @package  DeepskyLog
  * @author   Wim De Meester <deepskywim@gmail.com>
  * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
  * @link     http://www.deepskylog.org
  */
class Target extends Model
{
    /**
     * Targets have exactly one target type.
     *
     * @return HasOne The eloquent relationship
     */
    public function type()
    {
        return $this->hasOne('App\TargetType', 'id', 'type');
    }

    /**
     * Targets have exactly one or none constellations.
     *
     * @return HasOne The eloquent relationship
     */
    public function constellation()
    {
        return $this->hasOne('App\Constellations', 'id', 'con');
    }
}
