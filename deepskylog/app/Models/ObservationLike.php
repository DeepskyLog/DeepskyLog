<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationLike extends Model
{
    protected $fillable = ['user_id', 'observation_type', 'observation_id'];

    public function deepsky(): BelongsTo
    {
        return $this->belongsTo(Observation::class, 'observation_id');
    }

    public function comet(): BelongsTo
    {
        return $this->belongsTo(Observation::class, 'observation_id');
    }
}
