<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FilterMake extends Model
{
    protected $fillable = [
        'name',
    ];

    public function filters(): hasMany
    {
        return $this->hasMany(Filter::class, 'make_id');
    }
}
