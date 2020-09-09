<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EyepieceType extends Model
{
    protected $fillable = ['brand', 'type'];

    public $incrementing = false;
}
