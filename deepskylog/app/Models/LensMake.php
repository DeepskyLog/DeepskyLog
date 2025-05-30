<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LensMake extends Model
{
    protected $fillable = [
        'name',
    ];

    public function lenses(): hasMany
    {
        return $this->hasMany(Lens::class, 'make_id');
    }
}
