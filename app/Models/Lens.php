<?php

/**
 * Lens eloquent model.
 *
 * PHP Version 7
 *
 * @category Instruments
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Lens eloquent model.
 *
 * @category Instruments
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Lens extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'factor', 'active'];

    protected $table = 'lens';

    /**
     * Activate the lens.
     *
     * @param bool $active true to activate the lens, false to deactivate
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
     * Deactivate the lens.
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
        // Also method on user: lenses()
        return $this->belongsTo('App\Models\User');
    }

    // TODO: A lens belongs to one or more observations.
    //    public function observation()
    //    {
    //        return $this->belongsTo(Observation::class);
    //    }

    /**
     * Also store a thumbnail of the image.
     *
     * @param $media the media
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100);
    }

    /**
     * Return all lenses for use in a selection.
     *
     * @return None the method print the option tags
     */
    public static function getLensOptions()
    {
        $lenses = self::where(
            ['user_id' => Auth::user()->id]
        )->where(['active' => 1])->pluck('id', 'name');

        if (count($lenses) > 0) {
            echo '<option>' . _i('No default lens') . '</option>';

            foreach ($lenses as $name => $id) {
                if ($id == Auth::user()->stdlens) {
                    echo '<option selected="selected" value="' . $id . '}}">'
                           . $name . '</option>';
                } else {
                    echo '<option value="' . $id . '}}">' . $name . '</option>';
                }
            }
        } else {
            echo '<option>' . _i('Add a lens') . '</option>';
        }
    }
}