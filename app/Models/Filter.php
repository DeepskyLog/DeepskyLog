<?php

/**
 * Filter eloquent model.
 *
 * PHP Version 7
 *
 * @category Filters
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Filter eloquent model.
 *
 * @category Filters
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Filter extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'type', 'color', 'wratten', 'schott', 'active',
    ];

    /**
     * Activate or deactivate the filter.
     *
     * @return None
     */
    public function toggleActive()
    {
        if ($this->active) {
            $this->update(['active' => 0]);
        } else {
            $this->update(['active' => 1]);
        }
    }

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this lens belongs to
     */
    public function user()
    {
        // Also method on user: filters()
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get all of the sets for the filter.
     */
    public function sets()
    {
        return $this->morphToMany(Set::class, 'set_info');
    }

    /**
     * Returns the name of the filter type.
     *
     * @return string the name of the filter type
     */
    public function typeName()
    {
        return DB::table('filter_types')
            ->where('id', $this->type)->value('type');
    }

    /**
     * Returns the name of the filter color.
     *
     * @return string the name of the filter color
     */
    public function colorName()
    {
        return DB::table('filter_colors')
            ->where('id', $this->color)->value('color');
    }

    // TODO: A filter belongs to one or more observations.
    //    public function observation()
    //    {
    //        return $this->belongsTo(Observation::class);
    //    }

    /**
     * Also store a thumbnail of the image.
     *
     * @param $media the media
     *
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100);
    }
}
