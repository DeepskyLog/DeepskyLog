<?php

 /**
  * Instrument eloquent model.
  *
  * PHP Version 7
  *
  * @category Instruments
  * @author   Wim De Meester <deepskywim@gmail.com>
  * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
  * @link     http://www.deepskylog.org
  */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Instrument eloquent model.
 *
 * @category Instrument
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Instrument extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id', 'name', 'diameter', 'type',
        'fd', 'fixedMagnification', 'active',
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
    public function user()
    {
        // Also method on user: instruments()
        return $this->belongsTo('App\User');
    }

    /**
     * Returns the name of the instrument type.
     *
     * @return string the name of the instrument type
     */
    public function typeName()
    {
        return DB::table('instrument_types')
            ->where('id', $this->type)->value('type');
    }

    /**
     * Return all instruments, sorted by type for use in a selection.
     *
     * @return None the method print the optgroup and option tags
     */
    public static function getInstrumentOptions()
    {
        // Loop over the instrument types and make separate groups.
        $types = DB::table('instrument_types')->get();
        $count = 0;

        foreach ($types as $typeid => $type) {
            $instruments = self::where(
                ['user_id' => Auth::user()->id]
            )->where(['type' => $typeid])->where(['active' => 1])->pluck('id', 'name');

            if (count($instruments) > 0) {
                echo '<optgroup label="'._i($type->type).'">';

                foreach ($instruments as $name => $id) {
                    $count++;
                    if ($id == Auth::user()->stdtelescope) {
                        echo '<option selected="selected" value="'.$id.'}}">'
                           .$name.'</option>';
                    } else {
                        echo '<option value="'.$id.'}}">'.$name.'</option>';
                    }
                }
                echo '</optgroup>';
            }
        }

        if ($count === 0) {
            echo '<option>'._i('Add an instrument').'</option>';
        }
    }

    // TODO: An instrument belongs to one or more observations.
    //    public function observation()
    //    {
    //        return $this->belongsTo('App\Observation');
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
}
