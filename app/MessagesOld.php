<?php

/**
 * Old messages eloquent model.
 *
 * PHP Version 7
 *
 * @category Messages
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Old messages eloquent model.
 *
 * @category Messages
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class MessagesOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'messages';
}
