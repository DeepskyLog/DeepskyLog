<?php

/**
 * Old object names eloquent model.
 *
 * PHP Version 7
 *
 * @category Objects
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Old object names eloquent model.
 *
 * @category Objects
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class ObjectPartofOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'objectpartof';
}
