<?php

/**
 * Old observers eloquent model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Old observers eloquent model.
 */
class ObserversOld extends Model
{
    protected $casts = ['id' => 'string'];

    protected $connection = 'mysqlOld';

    protected $table = 'observers';
}
