<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EyepieceBrand extends Model
{
    protected $fillable = ['brand'];

    protected $primaryKey = 'brand';

    public $incrementing = false;
}
