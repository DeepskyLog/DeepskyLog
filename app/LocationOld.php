<?php

/**
 * Old locations eloquent model.
 *
 * PHP Version 7
 *
 * @category Locations
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Old locations eloquent model.
 *
 * @category Locations
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LocationOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'locations';
}
