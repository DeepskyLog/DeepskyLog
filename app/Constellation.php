<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Constellation extends Model
{
    public $incrementing = false;

    /**
     * Adds the link to the targets.
     *
     * @return BelongsTo the targets this constellation belongs to
     */
    public function target()
    {
        return $this->belongsTo('App\Target', 'con');
    }
}
