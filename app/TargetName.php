<?php

 /**
  * Target name eloquent model.
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
  * Target name eloquent model.
  *
  * @category Targets
  * @package  DeepskyLog
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
}
