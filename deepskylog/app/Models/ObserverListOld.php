<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObserverListOld extends Model
{
    public $timestamps = false;

    protected $connection = 'mysqlOld';

    protected $table = 'observerobjectlist';
}
