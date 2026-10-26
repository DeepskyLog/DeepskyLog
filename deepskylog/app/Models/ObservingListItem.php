<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservingListItem extends Model
{
    use ClearsResponseCache;
    use HasFactory;

    protected $fillable = [
        'observing_list_id',
        'object_name',
        'item_description',
        'source_mode',
        'source_observation_id',
        'added_by_user_id',
        'sort_order',
        'planned_at',
        'planned_from',
        'planned_until',
    ];

    protected $casts = [
        'source_mode' => 'string',
        'sort_order' => 'integer',
        'planned_at' => 'datetime',
        'planned_from' => 'datetime',
        'planned_until' => 'datetime',
    ];

    /**
     * New items go to the end of their list, so every page and export shows
     * the list in the order the objects were added.
     */
    protected static function booted(): void
    {
        static::creating(function (ObservingListItem $item) {
            if (empty($item->sort_order) && $item->observing_list_id) {
                $item->sort_order = (int) static::where('observing_list_id', $item->observing_list_id)->max('sort_order') + 1;
            }
        });
    }

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

    public function sourceObservation(): BelongsTo
    {
        return $this->belongsTo(Observation::class, 'source_observation_id');
    }

    /**
     * Guests only see public lists, so changes on a private list do not
     * clear the response cache.
     */
    protected function affectsCachedPages(): bool
    {
        return ObservingList::whereKey($this->observing_list_id)->where('public', true)->exists();
    }
}
