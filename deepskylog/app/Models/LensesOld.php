<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LensesOld extends Model
{
    protected $casts = ['id' => 'string'];

    protected $connection = 'mysqlOld';

    protected $table = 'lenses';
}
