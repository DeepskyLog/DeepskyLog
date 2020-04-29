<?php

/**
 * Old observers eloquent model.
 *
 * PHP Version 7
 *
 * @category Authentication
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Old observers eloquent model.
 *
 * @category Authentication
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class ObserversOld extends Model
{
    protected $casts = ['id' => 'string'];

    protected $connection = 'mysqlOld';

    protected $table = 'observers';
}
