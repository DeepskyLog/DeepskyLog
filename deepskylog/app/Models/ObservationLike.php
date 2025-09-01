<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationLike extends Model
{
    protected $fillable = ['user_id', 'observation_type', 'observation_id'];

    /**
     * Relation to a deepsky observation (old observations table).
     */
    public function deepsky(): BelongsTo
    {
        return $this->belongsTo(ObservationsOld::class, 'observation_id');
    }

    /**
     * Relation to a comet observation (old cometobservations table).
     */
    public function comet(): BelongsTo
    {
        return $this->belongsTo(CometObservationsOld::class, 'observation_id');
    }
}
