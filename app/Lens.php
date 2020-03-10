<?php

/**
 * Lens eloquent model.
 *
 * PHP Version 7
 *
 * @category Instruments
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Lens eloquent model.
 *
 * @category Instruments
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Lens extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['user_id', 'name', 'factor', 'active'];

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
        return $this->belongsTo('App\User');
    }

    // TODO: A lens belongs to one or more observations.
    //    public function observation()
    //    {
    //        return $this->belongsTo(Observation::class);
    //    }
}
