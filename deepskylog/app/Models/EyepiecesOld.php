<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EyepiecesOld extends Model
{
    protected $casts = ['id' => 'string'];

    protected $connection = 'mysqlOld';

    protected $table = 'eyepieces';
}
