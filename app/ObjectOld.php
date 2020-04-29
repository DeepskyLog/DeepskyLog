<?php

/**
 * Old objects eloquent model.
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
 * Old objects eloquent model.
 *
 * @category Objects
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class ObjectOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'objects';
}
