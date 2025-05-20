<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lens extends Model
{
    use Sluggable;

    protected $fillable = [
        'user_id', 'name', 'factor', 'make_id', 'active', 'observer', 'picture',
    ];

    protected $with = ['lens_make'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => false,
            ],
        ];
    }

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this instrument belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function lens_make(): BelongsTo
    {
        return $this->belongsTo(LensMake::class, 'make_id');
    }
}
