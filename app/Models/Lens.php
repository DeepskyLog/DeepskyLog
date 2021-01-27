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
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        // Also method on user: lenses()
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get all of the sets for the lens.
     */
    public function sets()
    {
        return $this->morphToMany(Set::class, 'set_info');
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
     * @param int $equipment_set The equipment set to use to find the instruments.  If 0, we want to see all equipment, if -1 we want to see all active instruments
     *
     * @return String the option tags
     */
    public static function getLensOptions(int $equipment_set = 0): string
    {
        if ($equipment_set == -1) {
            $lenses = self::where(
                ['user_id' => Auth::user()->id]
            )->where(['active' => 1])->pluck('id', 'name');
        } elseif ($equipment_set == 0) {
            $lenses = self::where(
                ['user_id' => Auth::user()->id]
            )->pluck('id', 'name');
        } else {
            $lenses = Set::where('id', $equipment_set)->first()->lenses()->pluck('id', 'name');
        }

        $toReturn = '';

        if (count($lenses) > 0) {
            $toReturn .= '<option>' . _i('No default lens') . '</option>';

            foreach ($lenses as $name => $id) {
                if ($id == Auth::user()->stdlens) {
                    $toReturn .= '<option selected="selected" value="' . $id . '">'
                           . $name . '</option>';
                } else {
                    $toReturn .= '<option value="' . $id . '">' . $name . '</option>';
                }
            }
        } else {
            $toReturn .= '<option>' . _i('No lens available') . '</option>';
        }
        return $toReturn;
    }
}
