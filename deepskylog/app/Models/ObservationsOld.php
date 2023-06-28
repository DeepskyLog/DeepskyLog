<?php

/**
 * Old observations eloquent model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Old observers eloquent model.
 */
class ObservationsOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'observations';
}
