<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationLike extends Model
{
    /**
     * A like of an observation, whatever its target, by the id of the
     * observation in the `observations` table (never the id of the old
     * DeepskyLog).
     */
    public const TYPE_OBSERVATION = 'observation';

    /** A like of an observing session, by the id of the session. */
    public const TYPE_SESSION = 'session';

    protected $fillable = ['user_id', 'observation_type', 'observation_id'];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class, 'observation_id');
    }
}
