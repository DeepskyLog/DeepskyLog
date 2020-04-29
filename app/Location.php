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

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
        return $this->belongsTo('App\User');
    }

    /**
     * Activate the location.
     *
     * @param bool $active true to activate the location, false to deactivate
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
     * Deactivate the location.
     *
     * @return None
     */
    public function inactive()
    {
        $this->active(false);
    }

    /**
     * Return all locations, sorted by country for use in a selection.
     *
     * @return None the method print the optgroup and option tags
     */
    public static function getLocationOptions()
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

        if (isset($translatedCountries) && count($translatedCountries) > 0) {
            ksort($translatedCountries);

            foreach ($translatedCountries as $countryid => $countryname) {
                echo '<optgroup label="'.$countryname.'">';

                $locations = self::where(
                    ['user_id' => Auth::user()->id]
                )->where(['active' => 1])->where(['country' => $countryid])
                ->pluck('id', 'name');

                foreach ($locations as $name => $id) {
                    if ($id == Auth::user()->stdlocation) {
                        echo '<option selected="selected" value="'.$id.'">'
                           .$name.'</option>';
                    } else {
                        echo '<option value="'.$id.'">'.$name.'</option>';
                    }
                }

                echo '</optgroup>';
            }
        } else {
            echo '<option>'._i('Add a location').'</option>';
        }
    }

    /**
     * Return the bortle value if the sqm value is given.
     *
     * @param float $sqm The sqm value
     *
     * @return int The bortle value
     */
    public static function getBortleFromSqm($sqm)
    {
        if ($sqm <= 17.5) {
            return 9;
        } elseif ($sqm <= 18.0) {
            return 8;
        } elseif ($sqm <= 18.5) {
            return 7;
        } elseif ($sqm <= 19.1) {
            return 6;
        } elseif ($sqm <= 20.4) {
            return 5;
        } elseif ($sqm <= 21.3) {
            return 4;
        } elseif ($sqm <= 21.5) {
            return 3;
        } elseif ($sqm <= 21.7) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * Return the limiting magnitude if the sqm value is given.
     *
     * @param float $sqm The sqm value
     *
     * @return float The limiting magnitude
     */
    public static function getLimitingMagnitudeFromSqm($sqm)
    {
        return 7.97 - 5 * log10(1 + pow(10, 4.316 - $sqm / 5.0));
    }

    /**
     * Return the sqm if the limiting magnitude if the sqm value is given.
     *
     * @param float $lm The limiting magnitude
     *
     * @return float The sqm value
     */
    public static function getSqmFromLimitingMagnitude($lm)
    {
        return 21.58 - 5 * log10(pow(10, (1.586 - $lm / 5.0)) - 1.0);
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
}
