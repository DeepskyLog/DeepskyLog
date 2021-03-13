<?php
/**
 * Location eloquent model.
 *
 * PHP Version 7
 *
 * @category Location
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Models;

use Carbon\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Spatie\MediaLibrary\InteractsWithMedia;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

/**
 * Location eloquent model.
 *
 * @category Location
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Location extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'active',
        'longitude', 'latitude', 'country',
        'elevation', 'timezone', 'limitingMagnitude',
        'skyBackground', 'bortle',
    ];

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this location belongs to
     */
    public function user()
    {
        // Also method on user: locations()
        return $this->belongsTo('App\Models\User');
    }

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
     * Return all locations, sorted by country for use in a selection.
     *
     * @return string the optgroup and option tags
     */
    public static function getLocationOptions(): string
    {
        // Select all the countries of the locations of the user
        $countries = self::where(
            ['user_id' => Auth::user()->id]
        )->where(['active' => 1])->pluck('country')->unique();

        foreach ($countries as $country) {
            $translatedCountries[$country] = \Countries::getOne(
                $country,
                \LaravelGettext::getLocaleLanguage()
            );
        }

        $toReturn = '';
        if (isset($translatedCountries) && count($translatedCountries) > 0) {
            ksort($translatedCountries);

            foreach ($translatedCountries as $countryid => $countryname) {
                $toReturn .= '<optgroup label="' . $countryname . '">';

                $locations = self::where(
                    ['user_id' => Auth::user()->id]
                )->where(['active' => 1])->where(['country' => $countryid])
                ->pluck('id', 'name');

                foreach ($locations as $name => $id) {
                    if ($id == Auth::user()->stdlocation) {
                        $toReturn .= '<option selected="selected" value="' . $id . '">'
                           . $name . '</option>';
                    } else {
                        $toReturn .= '<option value="' . $id . '">' . $name . '</option>';
                    }
                }

                $toReturn .= '</optgroup>';
            }
        } else {
            $toReturn .= '<option>' . _i('Add a location') . '</option>';
        }
        return $toReturn;
    }

    /**
     * Return all locations, to be used directly in choices.js
     *
     * @return string the string for choicesjs
     */
    public static function getLocationOptionsChoicesDetail(): string
    {
        $locations = self::where(
            ['user_id' => Auth::user()->id]
        )->where(['active' => 1])->whereNotNull(['skyBackground'])->orderByDesc('skyBackground')->pluck('id', 'name');

        $toReturn = '';
        if (count($locations) > 0) {
            foreach ($locations as $name => $id) {
                // Selected
                $sqm = ' (' . round(self::where(['id' => $id])->pluck('skyBackground')[0], 2) . ')';
                if ($id == Auth::user()->stdlocation) {
                    $toReturn .= "<option selected='selected' value='" . $id . "'>" . htmlentities($name, ENT_QUOTES) . $sqm . '</option>';
                } else {
                    $toReturn .= "<option value='" . $id . "'>" . htmlentities($name, ENT_QUOTES) . $sqm . '</option>';
                }
            }
        }

        if (count($locations) === 0) {
            $toReturn .= "<option value='0'>" . _i('No location available') . '</option>';
        }

        return $toReturn;
    }

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
     * Returns the length of the night plot for the year.
     *
     * @return string the image with the length of the night plot for the year
     */
    public function getLengthOfNightPlot(): string
    {
        $coords     = new GeographicalCoordinates($this->longitude, $this->latitude);
        $datestr    = Session::get('date');
        $date       = Carbon::createFromFormat('Y-m-d', $datestr);
        $date->hour = 12;

        $astrolib = new AstronomyLibrary($date, $coords);

        return $astrolib->getLengthOfNightPlot($this->timezone);
    }

    /**
     * Returns the sunrise, sunset and transit time for the location.
     *
     * @return string The sunrise / sunset / transit
     */
    public function sunriseSetTransit(): string
    {
        $datestr    = Session::get('date');
        $date       = Carbon::createFromFormat('Y-m-d', $datestr);
        $date->hour = 12;

        $sun_info = date_sun_info(
            $date->timestamp,
            $this->latitude,
            $this->longitude
        );

        if ($sun_info['sunrise'] === true) {
            $sunrise = '-';
        } elseif ($sun_info['sunrise'] === false) {
            $sunrise = '-';
        } else {
            $sunrise = Carbon::createFromTimestamp(
                $sun_info['sunrise']
            )->timezone($this->timezone)->isoFormat('HH:mm');
        }

        if ($sun_info['sunset'] === true) {
            $sunset = '-';
        } elseif ($sun_info['sunrise'] === false) {
            $sunset = '-';
        } else {
            $sunset = Carbon::createFromTimestamp(
                $sun_info['sunset']
            )->timezone($this->timezone)->isoFormat('HH:mm');
        }

        return $sunrise . ' / ' . $sunset . ' / ' .
            Carbon::createFromTimestamp(
                $sun_info['transit']
            )->timezone($this->timezone)->isoFormat('HH:mm');
    }

    /**
     * Returns the start and end of the civil twilight for the location.
     *
     * @return string The civil twilight
     */
    public function civilTwilight(): string
    {
        $datestr    = Session::get('date');
        $date       = Carbon::createFromFormat('Y-m-d', $datestr);
        $date->hour = 12;

        $sun_info = date_sun_info(
            $date->timestamp,
            $this->latitude,
            $this->longitude
        );

        if ($sun_info['civil_twilight_end'] === true) {
            $end = '-';
        } elseif ($sun_info['civil_twilight_end'] === false) {
            $end = '-';
        } else {
            $end = Carbon::createFromTimestamp(
                $sun_info['civil_twilight_end']
            )->timezone($this->timezone)->isoFormat('HH:mm');
        }

        if ($sun_info['civil_twilight_begin'] === true) {
            $start = '-';
        } elseif ($sun_info['civil_twilight_begin'] === false) {
            $start = '-';
        } else {
            $start = Carbon::createFromTimestamp(
                $sun_info['civil_twilight_begin']
            )->timezone($this->timezone)->isoFormat('HH:mm');
        }

        return $end . ' / ' . $start;
    }

    /**
     * Returns the start and end of the nautical twilight for the location.
     *
     * @return string The civil twilight
     */
    public function nauticalTwilight(): string
    {
        $datestr    = Session::get('date');
        $date       = Carbon::createFromFormat('Y-m-d', $datestr);
        $date->hour = 12;

        $sun_info = date_sun_info(
            $date->timestamp,
            $this->latitude,
            $this->longitude
        );

        if ($sun_info['nautical_twilight_end'] === true) {
            $end = '-';
        } elseif ($sun_info['nautical_twilight_end'] === false) {
            $end = '-';
        } else {
            $end = Carbon::createFromTimestamp(
                $sun_info['nautical_twilight_end']
            )->timezone($this->timezone)->isoFormat('HH:mm');
        }

        if ($sun_info['nautical_twilight_begin'] === true) {
            $start = '-';
        } elseif ($sun_info['nautical_twilight_begin'] === false) {
            $start = '-';
        } else {
            $start = Carbon::createFromTimestamp(
                $sun_info['nautical_twilight_begin']
            )->timezone($this->timezone)->isoFormat('HH:mm');
        }

        return $end . ' / ' . $start;
    }

    /**
     * Returns the start and end of the nautical twilight for the location.
     *
     * @return string The civil twilight
     */
    public function astronomicalTwilight(): string
    {
        $datestr    = Session::get('date');
        $date       = Carbon::createFromFormat('Y-m-d', $datestr);
        $date->hour = 12;

        $sun_info = date_sun_info(
            $date->timestamp,
            $this->latitude,
            $this->longitude
        );

        if ($sun_info['astronomical_twilight_end'] === true) {
            $end = '-';
        } elseif ($sun_info['astronomical_twilight_end'] === false) {
            $end = '-';
        } else {
            $end = Carbon::createFromTimestamp(
                $sun_info['astronomical_twilight_end']
            )->timezone($this->timezone)->isoFormat('HH:mm');
        }

        if ($sun_info['astronomical_twilight_begin'] === true) {
            $start = '-';
        } elseif ($sun_info['astronomical_twilight_begin'] === false) {
            $start = '-';
        } else {
            $start = Carbon::createFromTimestamp(
                $sun_info['astronomical_twilight_begin']
            )->timezone($this->timezone)->isoFormat('HH:mm');
        }

        return $end . ' / ' . $start;
    }
}
