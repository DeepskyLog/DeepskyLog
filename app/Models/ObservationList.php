<?php

namespace App\Models;

use Spatie\Tags\HasTags;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ObservationList extends Pivot
{
    use Sluggable;
    use HasTags;

    protected $fillable = [
        'user_id', 'name', 'description', 'discoverable',
    ];

    /**
     * Toggle the discoverable status of the list.
     *
     * @return None
     */
    public function toggleDiscoverable()
    {
        if ($this->discoverable) {
            $this->update(['discoverable' => 0]);
        } else {
            $this->update(['discoverable' => 1]);
        }
    }

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this lens belongs to
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }
}
