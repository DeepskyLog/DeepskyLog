<?php

/**
 * Instrument eloquent model.
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
use Illuminate\Support\Facades\DB;

/**
 * Instrument eloquent model.
 *
 * @category Instrument
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Instrument extends Model
{
    protected $fillable = [
        'observer_id', 'name', 'type', 'fd', 'fixedMagnification', 'active'
    ];

    /**
     * Activate the instrument.
     *
     * @param bool $active true to activate the instrument, false to deactivate
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
     * Deactivate the instrument.
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
     * @return BelongsTo the observer this instrument belongs to
     */
    public function observer()
    {
        // Also method on user: instruments()
        return $this->belongsTo('App\User');
    }

    /**
     * Returns the name of the instrument type.
     *
     * @return String The name of the instrument type.
     */
    public function typeName()
    {
        return DB::table('instrument_types')
            ->where('id', $this->type)->value('type');
    }

    // TODO: An instrument belongs to one or more observations.
    //    public function observation()
    //    {
    //        return $this->belongsTo('App\Observation');
    //    }
}
