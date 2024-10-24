<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectsOld extends Model
{
    public $timestamps = false;

    protected $connection = 'mysqlOld';

    protected $table = 'objects';

    public function long_type(): string
    {
        return __(''.TargetType::where('id', $this->type)->first()->type.'');
    }
}
