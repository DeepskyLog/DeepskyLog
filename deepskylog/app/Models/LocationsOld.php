<?php

/**
 * Old locations eloquent model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Old locations eloquent model.
 */
class LocationsOld extends Model
{
    protected $casts = ['id' => 'string'];

    protected $connection = 'mysqlOld';

    protected $table = 'locations';
}