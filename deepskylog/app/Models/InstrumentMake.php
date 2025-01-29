<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentMake extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function instruments(): hasMany
    {
        return $this->hasMany('App\Models\Instruments');
    }
}
