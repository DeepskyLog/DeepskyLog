<?php

namespace App\Traits;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Makes a sluggable model's slug unique among the models of the same user, as
 * the pages of equipment, locations and sets are found by user and slug.
 * Used instead of Sluggable, with 'unique' => true in sluggable().
 */
trait HasUniqueSlugPerUser
{
    use Sluggable;

    public function scopeWithUniqueSlugConstraints(Builder $query, Model $model, string $attribute, array $config, string $slug): Builder
    {
        return $query->where('user_id', $model->user_id);
    }
}
