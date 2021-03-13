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

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'diameter', 'type',
        'fd', 'fixedMagnification', 'active',
    ];

    /**
     * Activate or deactivate the instrument.
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
     * @return BelongsTo the observer this instrument belongs to
     */
    public function user()
    {
        // Also method on user: instruments()
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get all of the sets for the instrument.
     */
    public function sets()
    {
        return $this->morphToMany(Set::class, 'set_info');
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
     * @param int $equipment_set The equipment set to use to find the instruments.  If 0, we want to see all equipment, if -1 we want to see all active instruments
     *
     * @return String the optgroup and option tags
     */
    public static function getInstrumentOptions(int $equipment_set = 0): string
    {
        // Loop over the instrument types and make separate groups.
        $types = DB::table('instrument_types')->get();
        $count = 0;

        $toReturn = '';
        if (!auth()->user()->stdtelescope) {
            $toReturn .= '<optgroup><option value="NULL">' . _i('No default instrument') . '</option></optgroup>';
        }
        foreach ($types as $typeid => $type) {
            if ($equipment_set == -1) {
                $instruments = self::where(
                    ['user_id' => Auth::user()->id]
                )->where(['type' => $typeid])->where(['active' => 1])->pluck('id', 'name');
            } elseif ($equipment_set == 0) {
                $instruments = self::where(
                    ['user_id' => Auth::user()->id]
                )->where(['type' => $typeid])->pluck('id', 'name');
            } else {
                $instruments = Set::where('id', $equipment_set)->first()->instruments()->where(['type' => $typeid])->pluck('id', 'name');
            }

            if (count($instruments) > 0) {
                $toReturn .= '<optgroup label="' . _i($type->type) . '">';

                foreach ($instruments as $name => $id) {
                    $count++;
                    if ($id == Auth::user()->stdtelescope) {
                        $toReturn .= '<option selected="selected" value="' . $id . '">'
                           . $name . '</option>';
                    } else {
                        $toReturn .= '<option value="' . $id . '">' . $name . '</option>';
                    }
                }
                $toReturn .= '</optgroup>';
            }
        }

        if ($count === 0) {
            $toReturn = '<option>' . _i('No instrument available') . '</option>';
        }

        return $toReturn;
    }

    /**
     * Return all instruments, to be used directly in choices.js
     *
     * @param int $equipment_set The equipment set to use to find the instruments.  If 0, we want to see all equipment, if -1 we want to see all active instruments
     *
     * @return array the array for choicesjs
     */
    public static function getInstrumentOptionsChoices(int $equipment_set = 0): array
    {
        // Loop over the instrument types and make separate groups.
        $types = DB::table('instrument_types')->get();
        $count = 0;

        $returnArray = [];
        if (!auth()->user()->stdtelescope) {
            array_push($returnArray, 'NULL', _i('No default instrument'), 0, 1);
        }
        $counter = 1;

        $instrumentInSet = false;

        foreach ($types as $typeid => $type) {
            if ($equipment_set == -1) {
                $instruments = self::where(
                    ['user_id' => Auth::user()->id]
                )->where(['type' => $typeid])->where(['active' => 1])->pluck('id', 'name');
            } elseif ($equipment_set == 0) {
                $instruments = self::where(
                    ['user_id' => Auth::user()->id]
                )->where(['type' => $typeid])->pluck('id', 'name');
            } else {
                $instruments = Set::where('id', $equipment_set)->first()->instruments()->where(['type' => $typeid])->pluck('id', 'name');
            }

            if (count($instruments) > 0) {
                $counter++;

                foreach ($instruments as $name => $id) {
                    $count++;
                    array_push($returnArray, $id, htmlentities($name, ENT_QUOTES));
                    // Selected
                    if ($id == Auth::user()->stdtelescope) {
                        $instrumentInSet = true;
                        array_push($returnArray, 1);
                    } else {
                        array_push($returnArray, 0);
                    }
                    // Disabled
                    array_push($returnArray, 0);
                }
            }
        }
        if ($instrumentInSet) {
            array_unshift($returnArray, 0, htmlentities(_i('No instrument'), ENT_QUOTES), 0, 0);
        } else {
            array_unshift($returnArray, 0, htmlentities(_i('No instrument'), ENT_QUOTES), 1, 0);
        }

        if ($count === 0) {
            array_push($returnArray, 'NULL', _i('No instrument available'), 0, 1);
        }

        return $returnArray;
    }

    /**
     * Return all instruments, to be used directly in choices.js
     *
     * @return string the string for choicesjs
     */
    public static function getInstrumentOptionsChoicesDetail(): string
    {
        $instruments = self::where(
            ['user_id' => Auth::user()->id]
        )->where(['active' => 1])->orderByDesc('diameter')->pluck('id', 'name', 'diameter');

        $toReturn = '';
        if (count($instruments) > 0) {
            foreach ($instruments as $name => $id) {
                // Selected
                $diameter = self::where(['id' => $id])->pluck('diameter')[0];
                if (Auth::user()->showInches) {
                    $diameter       = round($diameter / 25.4, 2);
                    $diameterString = ' (' . $diameter . "'')";
                } else {
                    $diameterString = ' (' . $diameter . 'mm)';
                }
                if ($id == Auth::user()->stdtelescope) {
                    $toReturn .= "<option selected='selected' value='" . $id . "'>" . htmlentities($name, ENT_QUOTES) . $diameterString . '</option>';
                } else {
                    $toReturn .= "<option value='" . $id . "'>" . htmlentities($name, ENT_QUOTES) . $diameterString . '</option>';
                }
            }
        }

        if (count($instruments) === 0) {
            $toReturn .= "<option value='0'>" . _i('No instrument available') . '</option>';
        }

        return $toReturn;
    }

    // TODO: An instrument belongs to one or more observations.
    //    public function observation()
    //    {
    //        return $this->belongsTo('App\Models\Observation');
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
