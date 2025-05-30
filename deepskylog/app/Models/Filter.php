<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Filter extends Model
{
    use Sluggable;

    protected $fillable = [
        'user_id', 'name', 'make_id', 'type_id', 'color_id', 'wratten', 'schott', 'active', 'observer', 'picture',
    ];

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
     * @return BelongsTo the observer this filter belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function filter_make(): BelongsTo
    {
        return $this->belongsTo(FilterMake::class, 'make_id');
    }

    public function filter_type(): BelongsTo
    {
        return $this->belongsTo(FilterType::class, 'type_id');
    }

    public function filter_color(): BelongsTo
    {
        return $this->belongsTo(FilterColor::class, 'color_id');
    }
}
