<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FilterColor extends Model
{
    public function filters(): hasMany
    {
        return $this->hasMany(Filter::class, 'color_id');
    }
}
