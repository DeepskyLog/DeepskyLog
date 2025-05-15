<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EyepieceMake extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'name',
    ];

    public function eyepieces(): hasMany
    {
        return $this->hasMany(Eyepiece::class, 'make_id');
    }
}
