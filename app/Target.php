<?php

/**
 * Target eloquent model.
 *
 * PHP Version 7
 *
 * @category Targets
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use deepskylog\AstronomyLibrary\Time;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

/**
 * Target eloquent model.
 *
 * @category Targets
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Target extends Model
{
    private $_contrast;

    private $_popup;

    private $_target = null;

    private $_ephemerides;

    private $_location;

    private $_highestFromToAround;

    protected $fillable = ['name', 'type'];

    // These are the fields that are created dynamically, using the get...Attribute methods.
    protected $appends = ['rise', 'contrast', 'contrast_type', 'contrast_popup',
        'prefMag', 'prefMagEasy', 'rise_popup', 'transit', 'transit_popup',
        'set', 'set_popup', 'bestTime', 'maxAlt', 'maxAlt_popup',
        'highest_from', 'highest_around', 'highest_to', 'highest_alt', ];

    protected $primaryKey = 'name';

    private $_observationType = null;
    private $_targetType = null;

    public $incrementing = false;

    /**
     * Returns the contrast of the target.
     *
     * @return string The contrast of the target
     */
    public function getContrastAttribute()
    {
        if (!auth()->guest()) {
            if (!isset($this->_contrast)) {
                $this->_contrast = new \App\Contrast($this);
            }

            return $this->_contrast->contrast;
        }
    }

    /**
     * Returns the contrast type of the target, for showing
     * the correct background color.
     *
     * @return string The contrast type of the target
     */
    public function getContrastTypeAttribute()
    {
        if (!auth()->guest()) {
            if (!isset($this->_contrast)) {
                $this->_contrast = new \App\Contrast($this);
            }

            return $this->_contrast->contype;
        }
    }

    /**
     * Returns the text for the popup with the contrast of the target.
     *
     * @return string The popup with the contrast of the target
     */
    public function getContrastPopupAttribute()
    {
        if (!auth()->guest()) {
            if (!isset($this->_contrast)) {
                $this->_contrast = new \App\Contrast($this);
            }

            return $this->_contrast->popup;
        }
    }

    /**
     * Returns the preferred magnitude of the target, with
     * the information on the eyepiece / lens to use.
     *
     * @return string The preferred magnitude of the target
     */
    public function getPrefMagAttribute()
    {
        if (!auth()->guest()) {
            if (!isset($this->_contrast)) {
                $this->_contrast = new \App\Contrast($this);
            }

            return $this->_contrast->prefMag;
        }
    }

    /**
     * Returns the preferred magnitude of the target.
     *
     * @return string The preferred magnitude of the target
     */
    public function getPrefMagEasyAttribute()
    {
        if (!auth()->guest()) {
            if (!isset($this->_contrast)) {
                $this->_contrast = new \App\Contrast($this);
            }

            return $this->_contrast->prefMagEasy;
        }
    }

    /**
     * Returns the rise time of the target.
     *
     * @return string The rise time of the target
     */
    public function getRiseAttribute()
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_target->getRising() ? $this->_target->getRising()
            ->timezone($this->_location->timezone)->format('H:i') : '-';
    }

    /**
     * Returns the popup for the rise time of the target.
     *
     * @return string The popup for the rise time of the target
     */
    public function getRisePopupAttribute()
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_popup[0];
    }

    /**
     * Returns the transit time of the target.
     *
     * @return string The transit time of the target
     */
    public function getTransitAttribute()
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_target->getTransit()
            ->timezone($this->_location->timezone)->format('H:i');
    }

    /**
     * Returns the popup for the transit time of the target.
     *
     * @return string The popup for the transit time of the target
     */
    public function getTransitPopupAttribute()
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_popup[1];
    }

    /**
     * Returns the set time of the target.
     *
     * @return string The set time of the target
     */
    public function getSetAttribute()
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_target->getSetting() ? $this->_target->getSetting()
            ->timezone($this->_location->timezone)->format('H:i') : '-';
    }

    /**
     * Returns the popup for the set time of the target.
     *
     * @return string The popup for the set time of the target
     */
    public function getSetPopupAttribute()
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_popup[2];
    }

    /**
     * Returns the best time of the target.
     *
     * @return string The best time of the target
     */
    public function getBestTimeAttribute()
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_target->getBestTimeToObserve() ?
            $this->_target->getBestTimeToObserve()
            ->timezone($this->_location->timezone)->format('H:i') : '-';
    }

    /**
     * Returns the maximum altitude of the target.
     *
     * @return string The maximum altitude of the target
     */
    public function getMaxAltAttribute()
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_target->getMaxHeightAtNight() ?
            $this->_target->getMaxHeightAtNight()
            ->convertToDegrees() : '-';
    }

    /**
     * Returns the popup for the maximum altitude of the target.
     *
     * @return string The popup for the maximum altitude of the target
     */
    public function getMaxAltPopupAttribute()
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_popup[3];
    }

    /**
     * Returns the highest altitude of the target.
     *
     * @return string The highest altitude of the target
     */
    public function getHighestAltAttribute()
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_highestFromToAround[3];
    }

    /**
     * Returns the month from which the highest altitude is reached.
     *
     * @return string Returns the month from which the highest altitude is reached
     */
    public function getHighestFromAttribute()
    {
        if (!isset($this->_ephemerides)) {
            $this->getYearEphemerides();
        }

        return $this->_highestFromToAround[0];
    }

    /**
     * Returns the month around which the highest altitude is reached.
     *
     * @return string Returns the month around which the highest altitude is reached
     */
    public function getHighestAroundAttribute()
    {
        if (!isset($this->_ephemerides)) {
            $this->getYearEphemerides();
        }

        return $this->_highestFromToAround[1];
    }

    /**
     * Returns the month to which the highest altitude is reached.
     *
     * @return string Returns the month to which the highest altitude is reached
     */
    public function getHighestToAttribute()
    {
        if (!isset($this->_ephemerides)) {
            $this->getYearEphemerides();
        }

        return $this->_highestFromToAround[2];
    }

    /**
     * Returns the information on the rise, transit, and set times of the target.
     *
     * @return None
     */
    public function getRiseSetTransit()
    {
        if (!Auth::guest()) {
            if (Auth::user()->stdlocation != 0 && Auth::user()->stdtelescope != 0) {
                if ($this->isNonSolarSystem()) {
                    $datestr = Session::get('date');
                    $date = Carbon::createFromFormat('d/m/Y', $datestr);
                    $date->hour = 12;
                    if ($this->_location == null) {
                        $this->_location = \App\Location::where(
                            'id',
                            Auth::user()->stdlocation
                        )->first();
                    }
                    $location = $this->_location;

                    $geo_coords = new GeographicalCoordinates(
                        $location->longitude,
                        $location->latitude
                    );

                    $date->timezone($this->_location->timezone);

                    $this->_target = new
                        \deepskylog\AstronomyLibrary\Targets\Target();
                    $equa = new EquatorialCoordinates($this->ra, $this->decl);

                    // Add equatorial coordinates to the target.
                    $this->_target->setEquatorialCoordinates($equa);

                    $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich(
                        $date
                    );
                    $deltaT = Time::deltaT($date);

                    // Calculate the ephemerids for the target
                    $this->_target->calculateEphemerides(
                        $geo_coords,
                        $greenwichSiderialTime,
                        $deltaT
                    );

                    if ($this->_target->getMaxHeight()->getCoordinate() < 0.0) {
                        $popup[0] = sprintf(
                            _i('%s does not rise above horizon'),
                            $this->name
                        );
                        $popup[2] = $popup[0];
                    } elseif (!$this->_target->getRising()) {
                        $popup[0] = sprintf(_i('%s is circumpolar'), $this->name);
                        $popup[2] = $popup[0];
                    } else {
                        $popup[0] = sprintf(
                            _i('%s rises at %s in %s on ')
                                . $date->isoFormat('LL'),
                            $this->name,
                            $this->_target->getRising()
                                ->timezone($location->timezone)->format('H:i'),
                            $location->name
                        );
                        $popup[2] = sprintf(
                            _i('%s sets at %s in %s on ')
                                . $date->isoFormat('LL'),
                            $this->name,
                            $this->_target->getSetting()
                                ->timezone($location->timezone)->format('H:i'),
                            $location->name
                        );
                    }
                    $popup[1] = sprintf(
                        _i('%s transits at %s in %s on ')
                            . $date->isoFormat('LL'),
                        $this->name,
                        $this->_target->getTransit()
                            ->timezone($location->timezone)->format('H:i'),
                        $location->name
                    );

                    if ($this->_target->getMaxHeightAtNight()->getCoordinate() < 0) {
                        $popup[3] = sprintf(
                            _i('%s does not rise above horizon in %s on ')
                                . $date->isoFormat('LL'),
                            $this->name,
                            $location->name,
                            $datestr
                        );
                    } else {
                        $popup[3] = sprintf(
                            _i('%s reaches an altitude of %s in %s on ')
                                . $date->isoFormat('LL'),
                            $this->name,
                            trim(
                                $this->_target->getMaxHeightAtNight()
                                    ->convertToDegrees()
                            ),
                            $location->name,
                        );
                    }

                    $this->_popup = $popup;
                }
            }
        }
    }

    /**
     * Targets have exactly one target type.
     *
     * @return HasOne The eloquent relationship
     */
    public function type()
    {
        return $this->hasOne('App\TargetType', 'id', 'type');
    }

    /**
     * Targets have exactly one or none constellations.
     *
     * @return HasOne The eloquent relationship
     */
    public function constellation()
    {
        return $this->hasOne('App\Constellation', 'id', 'con');
    }

    /**
     * Returns the atlaspage of the target when the code of the atlas is given.
     *
     * @param string $atlasname The code of the atlas
     *
     * @return string The page where the target can be found in the atlas
     */
    public function atlasPage($atlasname)
    {
        return $this->$atlasname;
    }

    /**
     * Returns the declination as a human readable string.
     *
     * @return string The declination
     */
    public function declination()
    {
        $decl = $this->decl;
        $sign = ' ';
        if ($decl < 0) {
            $sign = '-';
            $decl = -$decl;
        }
        $decl_degrees = floor($decl);
        $subminutes = 60 * ($decl - $decl_degrees);
        $decl_minutes = floor($subminutes);
        $subseconds = 60 * ($subminutes - $decl_minutes);
        $decl_seconds = round($subseconds);
        if ($decl_seconds == 60) {
            $decl_seconds = 0;
            $decl_minutes++;
        }
        if ($decl_minutes == 60) {
            $decl_minutes = 0;
            $decl_degrees++;
        }

        return $sign . sprintf('%02d', $decl_degrees) . '°'
            . sprintf('%02d', $decl_minutes) . "'"
            . sprintf('%02d', $decl_seconds) . '"';
    }

    /**
     * Sets the observation types for the target.
     */
    private function _setObservationType()
    {
        $this->_targetType = $this->type()->first();
        $this->_observationType = $this->_targetType
            ->observationType()->first();
    }

    /**
     * Return the observation type and the target type for showing in the
     * detail page.
     *
     * @return string The Observation Type / Target Type
     */
    public function getObservationTypeAttribute()
    {
        if ($this->_observationType == null) {
            $this->_setObservationType();
        }

        return _i($this->_observationType['name'])
            . ' / ' . _i($this->_targetType['type']);
    }

    /**
     *  Check if the target is deepsky or a double star.
     *
     * @return bool true if the targer is deepsky or double star
     */
    public function isNonSolarSystem()
    {
        if ($this->_observationType == null) {
            $this->_setObservationType();
        }

        return $this->_observationType['type'] == 'ds'
            || $this->_observationType['type'] == 'double';
    }

    /**
     * Returns the right ascension as a human readable string.
     *
     * @return string The right ascension
     */
    public function ra()
    {
        $ra = $this->ra;
        $ra_hours = floor($ra);
        $subminutes = 60 * ($ra - $ra_hours);
        $ra_minutes = floor($subminutes);
        $ra_seconds = round(60 * ($subminutes - $ra_minutes));
        if ($ra_seconds == 60) {
            $ra_seconds = 0;
            $ra_minutes++;
        }
        if ($ra_minutes == 60) {
            $ra_minutes = 0;
            $ra_hours++;
        }
        if ($ra_hours == 24) {
            $ra_hours = 0;
        }

        return sprintf('%02d', $ra_hours) . 'h'
            . sprintf('%02d', $ra_minutes) . 'm'
            . sprintf('%02d', $ra_seconds) . 's';
    }

    /**
     * Returns the size of the target as a human readable string.
     *
     * @return string The size
     */
    public function size()
    {
        $size = '-';
        if ($this->diam1 != 0.0) {
            if ($this->diam1 >= 40.0) {
                if (round($this->diam1 / 60.0) == ($this->diam1 / 60.0)) {
                    if (($this->diam1 / 60.0) > 30.0) {
                        $size = sprintf("%.0f'", $this->diam1 / 60.0);
                    } else {
                        $size = sprintf("%.1f'", $this->diam1 / 60.0);
                    }
                } else {
                    $size = sprintf("%.1f'", $this->diam1 / 60.0);
                }
                if ($this->diam2 != 0.0) {
                    if (round($this->diam2 / 60.0) == ($this->diam2 / 60.0)) {
                        if (($this->diam2 / 60.0) > 30.0) {
                            $size = $size . sprintf("x%.0f'", $this->diam2 / 60.0);
                        } else {
                            $size = $size . sprintf("x%.1f'", $this->diam2 / 60.0);
                        }
                    } else {
                        $size = $size . sprintf("x%.1f'", $this->diam2 / 60.0);
                    }
                }
            } else {
                $size = sprintf('%.1f"', $this->diam1);
                if ($this->diam2 != 0.0) {
                    $size = $size . sprintf('x%.1f"', $this->diam2);
                }
            }
        }

        return $size;
    }

    /**
     * Returns the Field Of View of the target to be used in the aladin script.
     *
     * @return string The Field Of View
     */
    public function getFOV()
    {
        if (preg_match('/(?i)^AA\d*STAR$/', $this->type)
            || preg_match('/(?i)^PLNNB$/', $this->type)
            || $this->diam1 == 0 && $this->diam2 == 0
        ) {
            $fov = 1;
        } else {
            $fov = 2 * max($this->diam1, $this->diam2) / 3600;
        }

        return $fov;
    }

    /**
     * Returns the ra and dec of the target to be used in the aladin script.
     *
     * @return string The coordinates
     */
    public function raDecToAladin()
    {
        $decl = $this->decl;
        $ra = $this->ra;

        $sign = '';
        if ($decl < 0) {
            $sign = '-';
            $decl = -$decl;
        } else {
            $sign = '+';
        }

        $ra_hours = floor($ra);
        $subminutes = 60 * ($ra - $ra_hours);
        $ra_minutes = floor($subminutes);
        $ra_seconds = round(60 * ($subminutes - $ra_minutes));
        if ($ra_seconds == 60) {
            $ra_seconds = 0;
            $ra_minutes++;
        }
        if ($ra_minutes == 60) {
            $ra_minutes = 0;
            $ra_hours++;
        }
        if ($ra_hours == 24) {
            $ra_hours = 0;
        }

        $decl_degrees = floor($decl);
        $subminutes = 60 * ($decl - $decl_degrees);
        $decl_minutes = floor($subminutes);
        $decl_seconds = round(60 * ($subminutes - $decl_minutes));
        if ($decl_seconds == 60) {
            $decl_seconds = 0;
            $decl_minutes++;
        }
        if ($decl_minutes == 60) {
            $decl_minutes = 0;
            $decl_degrees++;
        }

        return $ra_hours . ' ' . $ra_minutes . ' ' . $ra_seconds . ' '
            . $sign . $decl_degrees . ' ' . $decl_minutes . ' ' . $decl_seconds;
    }

    /**
     * Returns the ephemerids for a whole year.
     * The ephemerids are calculated the first and the fifteenth of the month.
     *
     * @return array the ephemerides for a whole year
     */
    public function getYearEphemerides()
    {
        if (auth()->guest()) {
            return $this->_ephemerides;
        }
        if (isset($this->_ephemerides)) {
            return $this->_ephemerides;
        } else {
            if ($this->_location == null) {
                $this->_location = \App\Location::where(
                    'id',
                    Auth::user()->stdlocation
                )->first();
            }
            $location = $this->_location;
            $cnt = 0;

            $geo_coords = new GeographicalCoordinates(
                $location->longitude,
                $location->latitude
            );

            $target = new
                \deepskylog\AstronomyLibrary\Targets\Target();
            $equa = new EquatorialCoordinates($this->ra, $this->decl);

            // Add equatorial coordinates to the target.
            $target->setEquatorialCoordinates($equa);

            for ($i = 1; $i < 13; $i++) {
                for ($j = 1; $j < 16; $j = $j + 14) {
                    $datestr = sprintf('%02d', $j) . '/' . sprintf('%02d', $i) . '/'
                        . \Carbon\Carbon::now()->format('Y');
                    $date = Carbon::createFromFormat('d/m/Y', $datestr);
                    $date->hour = 12;
                    $date->timezone($this->_location->timezone);
                    $ephemerides[$cnt]['date'] = $date;

                    $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich(
                        $date
                    );
                    $deltaT = Time::deltaT($date);

                    // Calculate the ephemerids for the target
                    $target->calculateEphemerides(
                        $geo_coords,
                        $greenwichSiderialTime,
                        $deltaT
                    );

                    $nightephemerides = date_sun_info(
                        $date->getTimestamp(),
                        $location->latitude,
                        $location->longitude
                    );
                    $ephemerides[$cnt]['max_alt'] = trim(
                        $target->getMaxHeightAtNight()->convertToDegrees()
                    );
                    $ephemerides[$cnt]['transit'] = $target->getTransit()
                        ->timezone($this->_location->timezone)->format('H:i');
                    $ephemerides[$cnt]['rise'] = $target->getRising() ?
                        $target->getRising()->timezone($this->_location->timezone)
                        ->format('H:i') : '-';
                    $ephemerides[$cnt]['set'] = $target->getSetting() ?
                        $target->getSetting()->timezone($this->_location->timezone)
                        ->format('H:i') : '-';

                    $ephemerides[$cnt]['astronomical_twilight_end'] = is_bool(
                        $nightephemerides['astronomical_twilight_end']
                    ) ? null :
                        $date->copy()
                        ->setTimeFromTimeString(
                            date('H:i', $nightephemerides['astronomical_twilight_end'])
                        )->timezone($this->_location->timezone);

                    $ephemerides[$cnt]['astronomical_twilight_begin'] = is_bool(
                        $nightephemerides['astronomical_twilight_begin']
                    ) ? null :
                    $date->copy()
                        ->setTimeFromTimeString(
                            date('H:i', $nightephemerides['astronomical_twilight_begin'])
                        )->timezone($this->_location->timezone);

                    $ephemerides[$cnt]['nautical_twilight_end'] = is_bool(
                        $nightephemerides['nautical_twilight_end']
                    ) ? null : $date->copy()
                        ->setTimeFromTimeString(
                            date('H:i', $nightephemerides['nautical_twilight_end'])
                        )->timezone($this->_location->timezone);

                    $ephemerides[$cnt]['nautical_twilight_begin'] = is_bool(
                        $nightephemerides['nautical_twilight_begin']
                    ) ? null : $date->copy()
                        ->setTimeFromTimeString(
                            date('H:i', $nightephemerides['nautical_twilight_begin'])
                        )->timezone($this->_location->timezone);

                    if ($ephemerides[$cnt]['astronomical_twilight_end'] > $ephemerides[$cnt]['astronomical_twilight_begin']) {
                        $ephemerides[$cnt]['astronomical_twilight_begin']->addDay();
                    }
                    if ($ephemerides[$cnt]['nautical_twilight_end'] > $ephemerides[$cnt]['nautical_twilight_begin']) {
                        $ephemerides[$cnt]['nautical_twilight_begin']->addDay();
                    }
                    $ephemerides[$cnt]['count'] = ($j == 1) ? '' : $i;

                    $cnt++;
                }
            }

            // Setting the classes for the different colors
            $cnt = 0;
            foreach ($ephemerides as $ephem) {
                // Green if the max_alt does not change. This means that the
                // altitude is maximal
                if (($ephem['max_alt'] != '-'
                    && $ephemerides[($cnt + 1) % 24]['max_alt'] != '-')
                    && (($ephem['max_alt'] == $ephemerides[($cnt + 1) % 24]['max_alt'])
                    || ($ephem['max_alt'] == $ephemerides[($cnt + 23) % 24]['max_alt']))
                ) {
                    $ephemerides[$cnt]['max_alt_color'] = 'ephemeridesgreen';
                    $ephemerides[$cnt]['max_alt_popup']
                        = _i('%s reaches its highest altitude of the year', $this->name);
                } else {
                    $ephemerides[$cnt]['max_alt_color'] = '';
                    $ephemerides[$cnt]['max_alt_popup'] = '';
                }

                // Green if the transit is during astronomical twilight
                // Yellow if the transit is during nautical twilight
                $time = $ephem['date']->setTimeZone($location->timezone)->copy()
                    ->setTimeFromTimeString($ephem['transit']);
                if ($time->format('H') < 12) {
                    $time->addDay();
                }

                if ($ephem['max_alt'] != '-') {
                    if ($ephem['astronomical_twilight_end'] != null
                        && $time->between(
                            $ephem['astronomical_twilight_begin'],
                            $ephem['astronomical_twilight_end']
                        )
                    ) {
                        // Also add a popup explaining the color code: Issue 416
                        $ephemerides[$cnt]['transit_color'] = 'ephemeridesgreen';
                        $ephemerides[$cnt]['transit_popup'] = _i('%s reaches its highest altitude during the astronomical night', $this->name);
                    } elseif ($ephem['nautical_twilight_end'] != null
                        && $time->between(
                            $ephem['nautical_twilight_begin'],
                            $ephem['nautical_twilight_end']
                        )
                    ) {
                        $ephemerides[$cnt]['transit_color'] = 'ephemeridesyellow';
                        $ephemerides[$cnt]['transit_popup'] = _i('%s reaches its highest altitude during the nautical twilight', $this->name);
                    } else {
                        $ephemerides[$cnt]['transit_color'] = '';
                        $ephemerides[$cnt]['transit_popup'] = '';
                    }
                } else {
                    $ephemerides[$cnt]['transit_color'] = '';
                    $ephemerides[$cnt]['transit_popup'] = '';
                }

                $ephemerides[$cnt]['rise_color'] = '';
                $ephemerides[$cnt]['rise_popup'] = '';

                if ($ephem['max_alt'] == '-') {
                    $ephemerides[$cnt]['rise_color'] = '';
                } else {
                    if ($ephem['rise'] == '-') {
                        if ($ephem['astronomical_twilight_end'] != null) {
                            $ephemerides[$cnt]['rise_popup'] = _i('%s is visible during the night', $this->name);
                            $ephemerides[$cnt]['rise_color'] = 'ephemeridesgreen';
                        } elseif ($ephem['nautical_twilight_end'] != null) {
                            $ephemerides[$cnt]['rise_popup'] = _i('%s is visible during the nautical twilight', $this->name);
                            $ephemerides[$cnt]['rise_color'] = 'ephemeridesyellow';
                        }
                    }
                    if ($ephem['astronomical_twilight_end'] != null
                        && $this->_checkNightHourMinutePeriodOverlap(
                            $ephem['rise'],
                            $ephem['set'],
                            $ephem['astronomical_twilight_end'],
                            $ephem['astronomical_twilight_begin']
                        )
                    ) {
                        $ephemerides[$cnt]['rise_popup'] = _i('%s is visible during the night', $this->name);
                        $ephemerides[$cnt]['rise_color'] = 'ephemeridesgreen';
                    } elseif ($ephem['nautical_twilight_end'] != null
                        && $this->_checkNightHourMinutePeriodOverlap(
                            $ephem['rise'],
                            $ephem['set'],
                            $ephem['nautical_twilight_end'],
                            $ephem['nautical_twilight_begin']
                        )
                    ) {
                        $ephemerides[$cnt]['rise_color'] = 'ephemeridesyellow';
                        $ephemerides[$cnt]['rise_popup'] = _i('%s is visible during the nautical twilight', $this->name);
                    }
                }

                $cnt++;
            }

            $this->_ephemerides = $ephemerides;

            $collection = collect($ephemerides);
            $max_alt = $collection->max('max_alt');

            $filter = $collection->filter(
                function ($value) use ($max_alt) {
                    if ($value['max_alt'] == $max_alt) {
                        return true;
                    }
                }
            );

            $months = $filter->keys();

            if ($months->min() == 0 && $months->max() == 23) {
                $missing = collect(range(0, 23))->diff($months);

                for ($i = 0; $i < $missing->min(); $i++) {
                    $months[$i] += 24;
                }
            }
            $around = ($months->min()
                + ($months->max() - $months->min()) / 2) % 24 + 1;
            $from = $months->min() % 24 + 1;
            $to = $months->max() % 24 + 1;

            $this->_highestFromToAround[0] = $this->_convertToMonth($from);
            $this->_highestFromToAround[1] = $this->_convertToMonth($around);
            $this->_highestFromToAround[2] = $this->_convertToMonth($to);
            $this->_highestFromToAround[3] = $max_alt;

            return $ephemerides;
        }
    }

    /**
     * Converts a number from 1 to 24 to the name of the month.
     *
     * @param int $number The number of the half-month
     *
     * @return string The name of the month
     */
    private function _convertToMonth($number)
    {
        return ($number % 2 ? _i('mid') : _i('begin'))
                . ' '
                . date(
                    'M',
                    mktime(
                        0,
                        0,
                        0,
                        $number / 2,
                        1
                    )
                );
    }

    /**
     * Checks if there is an overlap between the two given time periods.
     *
     * @param string $firststart  the start of the first time interval
     * @param string $firstend    the end of the first time interval
     * @param Carbon $secondstart the start of the second time interval
     * @param Carbon $secondend   the end of the second time interval
     *
     * @return bool true if the two time intervals overlap
     */
    private function _checkNightHourMinutePeriodOverlap(
        $firststart,
        $firstend,
        $secondstart,
        $secondend
    ) {
        $firststartvalue = str_replace(':', '', $firststart);
        $firstendvalue = str_replace(':', '', $firstend);
        $secondstartvalue = $secondstart->format('Hi');
        $secondendvalue = $secondend->format('Hi');
        if ($secondstartvalue < $secondendvalue) {
            return (($firststartvalue > $secondstartvalue)
                && ($firststartvalue < $secondendvalue))
                || (($firstendvalue > $secondstartvalue)
                && ($firstendvalue < $secondendvalue))
                || (($firststartvalue < $secondend)
                && ($firstendvalue > $secondendvalue))
                || (($firststartvalue < $secondstartvalue)
                && ($firststartvalue > $firstendvalue))
                | (($firstendvalue > $secondendvalue)
                && ($firststartvalue > $firstendvalue));
        } else {
            return ($firststartvalue > $secondstartvalue)
                || ($firststartvalue < $secondendvalue)
                || ($firstendvalue > $secondstartvalue)
                || ($firstendvalue < $secondendvalue)
                || (($firststartvalue < $secondstartvalue)
                && ($firstendvalue > $secondendvalue)
                && ($firststartvalue > $firstendvalue));
        }
    }

    /**
     * Get a list with the nearby objects.
     *
     * @param int $dist The distance in arcminutes
     *
     * @return Collection The list with the nearby objects
     */
    public function getNearbyObjects($dist)
    {
        $dra = 0.0011 * $dist / cos($this->decl / 180.0 * 3.1415926535);

        return self::where('ra', '>', $this->ra - $dra)
            ->where('ra', '<', $this->ra + $dra)
            ->where('decl', '>', $this->decl - $dist / 60.0)
            ->where('decl', '<', $this->decl + $dist / 60.0);
    }

    /**
     * Returns the constellation of this target.
     *
     * @return string the constellation this target belongs to
     */
    public function getConstellation()
    {
        return \App\Constellation::where('id', $this->con)->first()->name;
    }
}
