<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentMake extends Model
{
    use ClearsResponseCache;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function instruments(): hasMany
    {
        return $this->hasMany(Instrument::class, 'make_id');
    }
}
