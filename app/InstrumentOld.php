<?php

/**
 * Old instruments eloquent model.
 *
 * PHP Version 7
 *
 * @category Instruments
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Old instruments eloquent model.
 *
 * @category Intruments
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class InstrumentOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'instruments';
}
