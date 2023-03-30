<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Atlas extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    public $timestamps = false;
}
