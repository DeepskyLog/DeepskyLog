<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ObservationType extends Model
{
    protected $primaryKey = 'type';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Adds the link to the target types.
     *
     * @return BelongsTo the target type this observation type belongs to
     */
    public function targetTypes()
    {
        return $this->belongsTo('App\TargetType', 'type', 'observation_type');
    }

    /**
     * Returns the targets of this observation type
     *
     * @return Collection The targets from this observation type
     */
    static public function targets($observation_type)
    {
        $col = collect();

        foreach (\App\TargetType::where('observation_type', $observation_type)->get() as $type) {
            $col = $col->toBase()->merge($type->targets()->get());
        }
        return $col;
    }

    /**
     * Returns the number of targets of this observation type
     *
     * @return Collection The number of targets from this observation type
     */
    static public function targetCount($observation_type)
    {
        $count = 0;

        foreach (\App\TargetType::where('observation_type', $observation_type)->get() as $type) {
            $count += $type->targets()->count();
        }
        return $count;
    }
}
