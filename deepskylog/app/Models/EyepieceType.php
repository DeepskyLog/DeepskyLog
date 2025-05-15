<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EyepieceType extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'name', 'eyepiece_makes_id',
    ];

    public function eyepieces(): hasMany
    {
        return $this->hasMany(Eyepiece::class, 'type_id');
    }
}
