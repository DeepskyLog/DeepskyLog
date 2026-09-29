<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObservationComment extends Model
{
    use ClearsResponseCache;
    use HasFactory;
    use SoftDeletes;

    /**
     * Comments are only made on observations, stored by the id of the
     * observation in the `observations` table.
     */
    public const TYPE_OBSERVATION = ObservationLike::TYPE_OBSERVATION;

    protected $fillable = [
        'observation_type',
        'observation_id',
        'user_id',
        'body',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A comment can be deleted by the author, the observation owner, or an admin.
     */
    public function canBeDeletedBy(User $user, ?User $observationOwner = null): bool
    {
        if ($user->id === $this->user_id || $user->isAdministrator()) {
            return true;
        }

        return $observationOwner !== null && (int) $observationOwner->id === (int) $user->id;
    }
}
