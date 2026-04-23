<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservingListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'observing_list_id',
        'object_name',
        'item_description',
        'source_mode',
        'source_observation_id',
        'added_by_user_id',
    ];

    protected $casts = [
        'source_mode' => 'string',
    ];

    /**
     * Get the observing list this item belongs to.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(ObservingList::class, 'observing_list_id');
    }

    /**
     * Get the user who added this item.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    /**
     * Get the source observation if autofilled.
     */
    public function sourceObservation()
    {
        return $this->belongsTo(ObservationsOld::class, 'source_observation_id');
    }
}
