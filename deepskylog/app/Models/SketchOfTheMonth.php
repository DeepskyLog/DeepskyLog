<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SketchOfTheMonth extends Model
{
    public $timestamps = false;

    protected $table = 'sketch_of_the_month';

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
