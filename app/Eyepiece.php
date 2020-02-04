<?php

/**
 * Eyepiece eloquent model.
 *
 * PHP Version 7
 *
 * @category Eyepieces
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * Eyepiece eloquent model.
 *
 * @category Eyepieces
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Eyepiece extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $fillable = [
        'user_id', 'name', 'focalLength', 'apparentFOV',
        'maxFocalLength', 'active', 'brand', 'type'
    ];

    /**
     * Activate the eyepiece.
     *
     * @param bool $active true to activate the eyepiece, false to deactivate
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
     * Deactivate the eyepiece.
     *
     * @return None
     */
    public function inactive()
    {
        $this->active(false);
    }

    /**
     * Returns the generic name of the eyepiece
     *
     * @return String The generic name of the eyepiece.
     */
    public function genericname()
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
        return $this->belongsTo('App\User');
    }

    // TODO: A filter belongs to one or more observations.
    //    public function observation()
    //    {
    //        return $this->belongsTo(Observation::class);
    //    }
}
