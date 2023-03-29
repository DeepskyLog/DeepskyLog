<?php

/**
 * Old instruments eloquent model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Old instruments eloquent model.
 */
class InstrumentsOld extends Model
{
    protected $casts = ['id' => 'string'];

    protected $connection = 'mysqlOld';

    protected $table = 'instruments';
}
