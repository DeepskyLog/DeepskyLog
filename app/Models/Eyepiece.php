<?php

/**
 * Eyepiece eloquent model.
 *
 * PHP Version 7
 *
 * @category Eyepieces
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
 * Eyepiece eloquent model.
 *
 * @category Eyepieces
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Eyepiece extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'focalLength', 'apparentFOV',
        'maxFocalLength', 'active', 'brand', 'type',
    ];

    /**
     * Activate or deactivate the eyepiece.
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
     * Returns the generic name of the eyepiece.
     *
     * @return string the generic name of the eyepiece
     */
    public function getGenericnameAttribute()
    {
        if ($this->brand != '') {
            if ($this->maxFocalLength != '') {
                return $this->focalLength . '-' . $this->maxFocalLength . 'mm '
                    . $this->brand . ' ' . $this->type;
            } else {
                return $this->focalLength . 'mm ' . $this->brand . ' ' . $this->type;
            }
        } else {
            return $this->name;
        }
    }

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this lens belongs to
     */
    public function user()
    {
        // Also method on user: eyepieces()
        return $this->belongsTo('App\Models\User');
    }

    // TODO: An eyepiece belongs to one or more observations.
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
     * Return all eyepieces for use in a selection.
     *
     * @return String the optgroup and option tags
     */
    public static function getEyepieceOptions(): string
    {
        $eyepieces = self::where(
            ['user_id' => Auth::user()->id]
        )->where(['active' => 1])->pluck('id', 'name');

        $toReturn = '';

        if (count($eyepieces) > 0) {
            $toReturn .= '<option>' . _i('No default eyepiece') . '</option>';

            foreach ($eyepieces as $name => $id) {
                if ($id == Auth::user()->stdeyepiece) {
                    $toReturn .= '<option selected="selected" value="' . $id . '">'
                           . $name . '</option>';
                } else {
                    $toReturn .= '<option value="' . $id . '">' . $name . '</option>';
                }
            }
        } else {
            $toReturn .= '<option>' . _i('Add an eyepiece') . '</option>';
        }
        return $toReturn;
    }
}
