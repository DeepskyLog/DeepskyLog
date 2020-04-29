<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Atlas extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    public $timestamps = false;
}
