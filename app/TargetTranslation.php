<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TargetTranslation extends Model
{
    protected $fillable = ['target_name'];
    public $timestamps = false;
}
