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

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
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

    protected $fillable = [
        'user_id', 'name', 'type', 'color', 'wratten', 'schott', 'active',
    ];

    /**
     * Activate the filter.
     *
     * @param bool $active true to activate the filter, false to deactivate
     *
     * @return None
     */
    public function active($active = true)
    {
        if ($active === false) {
            $this->update(['active' => 0]);
        } else {
            $this->update(compact('active'));
        }
    }

    /**
     * Deactivate the filter.
     *
     * @return None
     */
    public function inactive()
    {
        $this->active(false);
    }

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this lens belongs to
     */
    public function user()
    {
        // Also method on user: filters()
        return $this->belongsTo('App\User');
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
     * @return void
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100);
    }
}
