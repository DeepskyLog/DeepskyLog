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
    public function targetType()
    {
        return $this->belongsTo('App\TargetType', 'type', 'observation_type');
    }

    /**
     * Returns the targets of this observation type.
     *
     * @return Collection The targets from this observation type
     */
    public static function targets($observation_type)
    {
        $col = collect();

        $observationTypes = \App\TargetType::with('App\Target')->where(
            'observation_type',
            $observation_type
        )->get();
        foreach ($observationTypes as $type) {
            $col = $col->toBase()->merge($type->target()->get());
        }

        return $col;
    }

    /**
     * Returns the number of targets of this observation type.
     *
     * @param string $observation_type The observation type
     *
     * @return Collection The number of targets from this observation type
     */
    public static function targetCount($observation_type)
    {
        $count = 0;

        // Eager loading and directly counting the targets
        $types = \App\TargetType::withCount('target')
            ->where('observation_type', $observation_type)->get();

        foreach ($types as $type) {
            // The counts of the targets is in target_count
            $count += $type->target_count;
        }

        return $count;
    }
}
