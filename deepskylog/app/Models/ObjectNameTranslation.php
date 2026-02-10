<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectNameTranslation extends Model
{
    protected $table = 'object_name_translations';

    protected $fillable = [
        'objectname',
        'locale',
        'name',
    ];
}
