<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObservationLike extends Model
{
    protected $fillable = ['user_id', 'observation_type', 'observation_id'];
}
