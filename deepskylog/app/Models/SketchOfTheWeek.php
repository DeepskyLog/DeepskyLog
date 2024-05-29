<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SketchOfTheWeek extends Model
{
    public $timestamps = false;

    protected $table = 'sketch_of_the_week';

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
