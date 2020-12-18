<?php

/**
 * Target eloquent model.
 *
 * PHP Version 7
 *
 * @category Targets
 *
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */

namespace App\Models;

use Carbon\Carbon;
use RuntimeException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use deepskylog\AstronomyLibrary\Time;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\HasOne;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

/**
 * Target eloquent model.
 *
 * @category Targets
 *
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */
class Target extends Model
{
    use HasTranslations;

    private $_contrast;

    private $_popup;

    private $_target = null;

    private $_ephemerides;

    private $_location;

    private $_highestFromToAround;

    protected $fillable = ['target_type'];

    // These are the fields that are created dynamically, using the get...Attribute methods.
    protected $appends = ['rise', 'contrast', 'contrast_type', 'contrast_popup',
        'prefMag', 'prefMagEasy', 'rise_popup', 'transit', 'transit_popup',
        'set', 'set_popup', 'bestTime', 'maxAlt', 'maxAlt_popup',
        'highest_from', 'highest_around', 'highest_to', 'highest_alt', 'slug', ];

    public $translatable = ['target_name'];
    //protected $primaryKey = 'target_name';

    private $_observationType = null;
    private $_targetType      = null;

    public $incrementing = true;

    /**
     * Returns the contrast of the target.
     *
     * @return string The contrast of the target
     */
    public function getContrastAttribute(): string
    {
        if (!auth()->guest()) {
            if (auth()->user()->stdtelescope && auth()->user()->stdlocation) {
                if (!isset($this->_contrast)) {
                    $this->_contrast = new \App\Models\Contrast($this);
                }

                return $this->_contrast->contrast;
            }
        }

        return '-';
    }

    /**
     * Returns the slug of the target
     *
     * @return string The slug of the target
     */
    public function getSlugAttribute(): string
    {
        return \App\Models\TargetName::where('altname', $this->getTranslation('target_name', 'en'))->first()->slug;
    }

    /**
     * Returns the contrast type of the target, for showing
     * the correct background color.
     *
     * @return string The contrast type of the target
     */
    public function getContrastTypeAttribute(): string
    {
        if (!auth()->guest()) {
            if (auth()->user()->stdtelescope && auth()->user()->stdlocation) {
                if (!isset($this->_contrast)) {
                    $this->_contrast = new \App\Models\Contrast($this);
                }

                return $this->_contrast->contype;
            }
        }

        return '-';
    }

    /**
     * Returns the text for the popup with the contrast of the target.
     *
     * @return string The popup with the contrast of the target
     */
    public function getContrastPopupAttribute(): string
    {
        if (!auth()->guest()) {
            if (auth()->user()->stdtelescope && auth()->user()->stdlocation) {
                if (!isset($this->_contrast)) {
                    $this->_contrast = new \App\Models\Contrast($this);
                }

                return $this->_contrast->popup;
            }
        }

        return '-';
    }

    /**
     * Returns the preferred magnitude of the target, with
     * the information on the eyepiece / lens to use.
     *
     * @return string The preferred magnitude of the target
     */
    public function getPrefMagAttribute(): string
    {
        if (!auth()->guest()) {
            if (auth()->user()->stdtelescope && auth()->user()->stdlocation) {
                if (!isset($this->_contrast)) {
                    $this->_contrast = new \App\Models\Contrast($this);
                }

                return $this->_contrast->prefMag;
            }
        }

        return '-';
    }

    /**
     * Returns the preferred magnitude of the target.
     *
     * @return string The preferred magnitude of the target
     */
    public function getPrefMagEasyAttribute(): string
    {
        if (!auth()->guest()) {
            if (auth()->user()->stdtelescope && auth()->user()->stdlocation && auth()->user()->stdlocation) {
                if (!isset($this->_contrast)) {
                    $this->_contrast = new \App\Models\Contrast($this);
                }

                return $this->_contrast->prefMagEasy;
            }
        }

        return '-';
    }

    /**
     * Returns the rise time of the target.
     *
     * @return string The rise time of the target
     */
    public function getRiseAttribute(): string
    {
        $rise = '-';

        if (!Auth::guest()) {
            if (Auth::user()->stdlocation) {
                if (!$this->_target) {
                    $this->getRiseSetTransit();
                }
            }
            if (Auth::user()->stdlocation && Auth::user()->stdtelescope) {
                try {
                    $riseObj = $this->_target->getRising();
                    if ($riseObj) {
                        $rise = $riseObj->timezone($this->_location->timezone)->format('H:i');
                    }
                } catch (RuntimeException $ex) {
                    $rise = '-';
                }
            }
        }

        return $rise;
    }

    /**
     * Returns the popup for the rise time of the target.
     *
     * @return string The popup for the rise time of the target
     */
    public function getRisePopupAttribute(): string
    {
        if (!auth()->guest()) {
            if (!$this->_target) {
                $this->getRiseSetTransit();
            }

            return $this->_popup[0];
        } else {
            return '-';
        }
    }

    /**
     * Returns the transit time of the target.
     *
     * @return string The transit time of the target
     */
    public function getTransitAttribute(): string
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        $transit = '-';
        if (!Auth::guest()) {
            if (Auth::user()->stdlocation) {
                $transit = $this->_target->getTransit() ? $this->_target->getTransit()
                    ->timezone($this->_location->timezone)->format('H:i') : '-';
            }
        }

        return $transit;
    }

    /**
     * Returns the popup for the transit time of the target.
     *
     * @return string The popup for the transit time of the target
     */
    public function getTransitPopupAttribute(): string
    {
        if (Auth::guest()) {
            return '-';
        }
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
    public function getSetAttribute(): string
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        $set = '-';
        if (!Auth::guest()) {
            if (Auth::user()->stdlocation) {
                $set = $this->_target->getSetting() ? $this->_target->getSetting()
                    ->timezone($this->_location->timezone)->format('H:i') : '-';
            }
        }

        return $set;
    }

    /**
     * Returns the popup for the set time of the target.
     *
     * @return string The popup for the set time of the target
     */
    public function getSetPopupAttribute(): string
    {
        if (Auth::guest()) {
            return '-';
        }

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
    public function getBestTimeAttribute(): string
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        $best = '-';
        if (!Auth::guest()) {
            if (Auth::user()->stdlocation) {
                $best = $this->_target->getBestTimeToObserve() ?
                    $this->_target->getBestTimeToObserve()
                    ->timezone($this->_location->timezone)->format('H:i') : '-';
            }
        }

        return $best;
    }

    /**
     * Returns the maximum altitude of the target.
     *
     * @return string The maximum altitude of the target
     */
    public function getMaxAltAttribute(): string
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        $maxalt = '-';
        if (!Auth::guest()) {
            if (Auth::user()->stdlocation) {
                $maxalt = $this->_target->getMaxHeightAtNight() ?
                $this->_target->getMaxHeightAtNight()
                    ->convertToShortDegrees() : '-';
            }
        }

        return $maxalt;
    }

    /**
     * Returns the popup for the maximum altitude of the target.
     *
     * @return string The popup for the maximum altitude of the target
     */
    public function getMaxAltPopupAttribute(): string
    {
        if (Auth::guest()) {
            return '-';
        }
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
    public function getHighestAltAttribute(): string
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }
        $high = '-';
        if (!Auth::guest()) {
            if (Auth::user()->stdlocation) {
                $high = $this->_target->getMaxHeight() ?
                $this->_target->getMaxHeight()
                    ->convertToShortDegrees() : '-';
            }
        }

        return $high;
    }

    /**
     * Returns the month from which the highest altitude is reached.
     *
     * @return string Returns the month from which the highest altitude is reached
     */
    public function getHighestFromAttribute(): string
    {
        if (!isset($this->_ephemerides)) {
            $this->getYearEphemerides();
        }

        $high = '-';
        if (!Auth::guest()) {
            if (Auth::user()->stdlocation) {
                $high = $this->_highestFromToAround[0];
            }
        }

        return $high;
    }

    /**
     * Returns the month around which the highest altitude is reached.
     *
     * @return string Returns the month around which the highest altitude is reached
     */
    public function getHighestAroundAttribute(): string
    {
        if (!isset($this->_ephemerides)) {
            $this->getYearEphemerides();
        }

        $high = '-';
        if (!Auth::guest()) {
            if (Auth::user()->stdlocation) {
                $high = $this->_highestFromToAround[1];
            }
        }

        return $high;
    }

    /**
     * Returns the month to which the highest altitude is reached.
     *
     * @return string Returns the month to which the highest altitude is reached
     */
    public function getHighestToAttribute(): string
    {
        if (!isset($this->_ephemerides)) {
            $this->getYearEphemerides();
        }

        $high = '-';
        if (!Auth::guest()) {
            if (Auth::user()->stdlocation) {
                $high = $this->_highestFromToAround[2];
            }
        }

        return $high;
    }

    /**
     * Returns the information on the rise, transit, and set times of the target.
     *
     * @return None
     */
    public function getRiseSetTransit(): void
    {
        if (!$this->_observationType) {
            $this->_setObservationType();
        }

        if ($this->_observationType['type'] == 'sun') {
            $this->_target = new \deepskylog\AstronomyLibrary\Targets\Sun();
        } elseif ($this->_observationType['type'] == 'planets') {
            switch ($this->getTranslation('target_name', 'en')) {
                case 'Mercury':
                    $this->_target = new \deepskylog\AstronomyLibrary\Targets\Mercury();
                    break;
                case 'Venus':
                    $this->_target = new \deepskylog\AstronomyLibrary\Targets\Venus();
                    break;
                case 'Mars':
                    $this->_target = new \deepskylog\AstronomyLibrary\Targets\Mars();
                    break;
                case 'Jupiter':
                    $this->_target = new \deepskylog\AstronomyLibrary\Targets\Jupiter();
                    break;
                case 'Saturn':
                    $this->_target = new \deepskylog\AstronomyLibrary\Targets\Saturn();
                    break;
                case 'Uranus':
                    $this->_target = new \deepskylog\AstronomyLibrary\Targets\Uranus();
                    break;
                case 'Neptune':
                    $this->_target = new \deepskylog\AstronomyLibrary\Targets\Neptune();
                    break;

                default:
            break;
            }
        } else {
            $this->_target = new \deepskylog\AstronomyLibrary\Targets\Target();
            $equa          = new EquatorialCoordinates($this->ra, $this->decl);

            // Add equatorial coordinates to the target.
            $this->_target->setEquatorialCoordinates($equa);
        }
        $this->_popup[0] = '-';
        $this->_popup[1] = '-';
        $this->_popup[2] = '-';
        $this->_popup[3] = '-';

        $datestr    = Session::get('date');
        $date       = Carbon::createFromFormat('Y-m-d', $datestr);
        $date->hour = 12;

        if (!Auth::guest() && Auth::user()->stdlocation) {
            if ($this->_location == null) {
                $this->_location = \App\Models\Location::where(
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
        }

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich(
            $date
        );
        $deltaT = Time::deltaT($date);

        if ($this->isSolarSystem()) {
            $nutation = Time::nutation($deltaT);

            if ($this->_observationType['type'] == 'sun') {
                $this->_target->calculateEquatorialCoordinatesHighAccuracy($date, $nutation);
            } elseif ($this->_observationType['type'] == 'planets') {
                $this->_target->calculateEquatorialCoordinates($date);
            }
        }

        if (!Auth::guest()) {
            if (Auth::user()->stdlocation) {
                if ($this->isNonSolarSystem() || $this->isSolarSystem()) {
                    // Calculate the ephemerids for the target
                    $this->_target->calculateEphemerides(
                        $geo_coords,
                        $greenwichSiderialTime,
                        $deltaT
                    );
                    if ($this->_target->getMaxHeight()->getCoordinate() < 0.0) {
                        $popup[0] = sprintf(
                            _i('%s does not rise above horizon'),
                            $this->target_name
                        );
                        $popup[2] = $popup[0];
                    } elseif (!$this->_target->getRising()) {
                        $popup[0] = sprintf(
                            _i('%s is circumpolar'),
                            $this->target_name
                        );
                        $popup[2] = $popup[0];
                    } else {
                        $popup[0] = sprintf(
                            _i('%s rises at %s in %s on ')
                                . $date->isoFormat('LL'),
                            $this->target_name,
                            $this->_target->getRising()
                                ->timezone($location->timezone)->format('H:i'),
                            $location->target_name
                        );
                        $popup[2] = sprintf(
                            _i('%s sets at %s in %s on ')
                                . $date->isoFormat('LL'),
                            $this->target_name,
                            $this->_target->getSetting()
                                ->timezone($location->timezone)->format('H:i'),
                            $location->target_name
                        );
                    }
                    $popup[1] = sprintf(
                        _i('%s transits at %s in %s on ')
                            . $date->isoFormat('LL'),
                        $this->target_name,
                        $this->_target->getTransit()
                            ->timezone($location->timezone)->format('H:i'),
                        $location->target_name
                    );

                    if ($this->_target->getMaxHeightAtNight()->getCoordinate() < 0) {
                        $popup[3] = sprintf(
                            _i('%s does not rise above horizon in %s on ')
                                . $date->isoFormat('LL'),
                            $this->target_name,
                            $location->target_name,
                            $datestr
                        );
                    } else {
                        $popup[3] = sprintf(
                            _i('%s reaches an altitude of %s in %s on ')
                                . $date->isoFormat('LL'),
                            $this->target_name,
                            trim(
                                $this->_target->getMaxHeightAtNight()
                                    ->convertToDegrees()
                            ),
                            $location->target_name,
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
    public function type(): HasOne
    {
        return $this->hasOne('App\Models\TargetType', 'id', 'target_type');
    }

    /**
     * Targets have exactly one or none constellations.
     *
     * @return HasOne The eloquent relationship
     */
    public function constellation(): HasOne
    {
        return $this->hasOne('App\Models\Constellation', 'id', 'constellation');
    }

    /**
     * Returns the constellation of this target.
     *
     * @return string the constellation this target belongs to
     */
    public function getConstellation()
    {
        if ($this->constellation) {
            return \App\Models\Constellation::where('id', $this->constellation)->first()->name;
        } else {
            return \App\Models\Constellation::where(
                'id',
                $this->_target->getConstellation()
            )->first()->name;
        }
    }

    /**
     * Returns the short name of the constellation.
     *
     * @return string The short name of the constellation
     */
    public function getConstellationShortAttribute(): string
    {
        return $this->constellation;
    }

    /**
     * Returns the atlaspage of the target when the code of the atlas is given.
     *
     * @param string $atlasname The code of the atlas
     *
     * @return string The page where the target can be found in the atlas
     */
    public function atlasPage($atlasname): string
    {
        if ($this->atlasname) {
            return $this->$atlasname;
        } else {
            return $this->_target->getEquatorialCoordinates()->calculateAtlasPage($atlasname);
        }
    }

    /**
     * Returns the declination as a human readable string.
     *
     * @return string The declination
     */
    public function declination(): string
    {
        return $this->_target->getEquatorialCoordinates()
            ->getDeclination()->convertToDegrees();
    }

    /**
     * Sets the observation types for the target.
     *
     * @return None
     */
    private function _setObservationType(): void
    {
        $this->_targetType      = $this->type()->first();
        $this->_observationType = $this->_targetType
            ->observationType()->first();
    }

    /**
     * Return the observation type and the target type for showing in the
     * detail page.
     *
     * @return string The Observation Type / Target Type
     */
    public function getObservationTypeAttribute(): string
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
     * @return bool true if the target is deepsky or double star
     */
    public function isNonSolarSystem(): bool
    {
        if ($this->_observationType == null) {
            $this->_setObservationType();
        }

        return $this->_observationType['type'] == 'ds'
            || $this->_observationType['type'] == 'double';
    }

    /**
     *  Check if the target is a solar system target.
     *
     * @return bool true if the target is a solar system target
     */
    public function isSolarSystem(): bool
    {
        return $this->_observationType['type'] == 'sun'
//            || $this->_observationType['type'] == 'asteroids'
//            || $this->_observationType['type'] == 'comets'
//            || $this->_observationType['type'] == 'moon'
            || $this->_observationType['type'] == 'planets';
    }

    /**
     *  Check if the target is the sun.
     *
     * @return bool true if the target is the sun
     */
    public function isSun(): bool
    {
        return $this->_observationType['type'] == 'sun';
    }

    /**
     * Returns the right ascension as a human readable string.
     *
     * @return string The right ascension
     */
    public function ra(): string
    {
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        return $this->_target->getEquatorialCoordinates()
            ->getRA()->convertToHours();
    }

    /**
     * Returns the size of the target as a human readable string.
     *
     * @return string The size
     */
    public function size(): string
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
    public function getFOV(): string
    {
        $standard = true;

        if (auth()->check()) {
            if (auth()->user()->stdlens) {
                $factor = Lens::where(
                    'id',
                    auth()->user()->stdlens
                )->first()->factor;
            } else {
                $factor = 1.0;
            }
            if (auth()->user()->stdtelescope) {
                $instrument = Instrument::where(
                    'id',
                    auth()->user()->stdtelescope
                )->first();
                if ($instrument->fd) {
                    $focalLength = $instrument->diameter * $instrument->fd * $factor;
                    if (auth()->user()->stdeyepiece) {
                        $eyepiece = Eyepiece::where(
                            'id',
                            auth()->user()->stdeyepiece
                        )->first();
                        $magnification = $focalLength / $eyepiece->focalLength;
                        $fov           = $eyepiece->apparentFOV / $magnification;
                        $standard      = false;
                    }
                }
            }
        }

        if ($standard) {
            if (preg_match('/(?i)^AA\d*STAR$/', $this->type)
                || preg_match('/(?i)^PLNNB$/', $this->type)
                || $this->diam1 == 0 && $this->diam2 == 0
            ) {
                $fov = 1;
            } else {
                $fov = 2 * max($this->diam1, $this->diam2) / 3600;
            }
        }

        return $fov;
    }

    /**
     * Returns the ra and dec of the target to be used in the aladin script.
     *
     * @return string The coordinates
     */
    public function raDecToAladin(): string
    {
        return str_replace(
            '  ',
            ' +',
            str_replace(
                'h',
                ' ',
                str_replace(
                    'm',
                    ' ',
                    str_replace(
                        's',
                        '',
                        str_replace(
                            '°',
                            ' ',
                            str_replace(
                                "'",
                                ' ',
                                str_replace(
                                    '"',
                                    '',
                                    $this->_target->getEquatorialCoordinates()
                                        ->getRA()->convertToHours()
                                    . ' '
                                    . $this->_target->getEquatorialCoordinates()
                                        ->getDeclination()->convertToDegrees()
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Returns the ephemerids for a whole year.
     * The ephemerids are calculated the first and the fifteenth of the month.
     *
     * @return array the ephemerides for a whole year
     */
    public function getYearEphemerides(): ?array
    {
        if (auth()->guest()) {
            return $this->_ephemerides;
        }
        if (!auth()->user()->stdlocation) {
            return $this->_ephemerides;
        }
        if (isset($this->_ephemerides)) {
            return $this->_ephemerides;
        } else {
            if ($this->_location == null) {
                $this->_location = \App\Models\Location::where(
                    'id',
                    Auth::user()->stdlocation
                )->first();
            }
            $location = $this->_location;
            $cnt      = 0;

            $geo_coords = new GeographicalCoordinates(
                $location->longitude,
                $location->latitude
            );

            if (!$this->_observationType) {
                $this->_setObservationType();
            }

            if ($this->_observationType['type'] == 'sun') {
                $target     = new \deepskylog\AstronomyLibrary\Targets\Sun();
            } elseif ($this->_observationType['type'] == 'planets') {
                switch ($this->getTranslation('target_name', 'en')) {
                    case 'Mercury':
                        $target = new \deepskylog\AstronomyLibrary\Targets\Mercury();
                        break;
                    case 'Venus':
                        $target = new \deepskylog\AstronomyLibrary\Targets\Venus();
                        break;
                    case 'Mars':
                        $target = new \deepskylog\AstronomyLibrary\Targets\Mars();
                        break;
                    case 'Jupiter':
                        $target = new \deepskylog\AstronomyLibrary\Targets\Jupiter();
                        break;
                    case 'Saturn':
                        $target = new \deepskylog\AstronomyLibrary\Targets\Saturn();
                        break;
                    case 'Uranus':
                        $target = new \deepskylog\AstronomyLibrary\Targets\Uranus();
                        break;
                    case 'Neptune':
                        $target = new \deepskylog\AstronomyLibrary\Targets\Neptune();
                        break;

                    default:
                break;
                }
            } else {
                $target = new
                \deepskylog\AstronomyLibrary\Targets\Target();
                $equa = new EquatorialCoordinates($this->ra, $this->decl);
                // Add equatorial coordinates to the target.
                $target->setEquatorialCoordinates($equa);
            }

            for ($i = 1; $i < 13; ++$i) {
                for ($j = 1; $j < 16; $j = $j + 14) {
                    $datestr = sprintf('%02d', $j) . '/' . sprintf('%02d', $i) . '/'
                        . \Carbon\Carbon::now()->format('Y');
                    $date       = Carbon::createFromFormat('d/m/Y', $datestr);
                    $date->hour = 12;
                    $date->timezone($this->_location->timezone);
                    $ephemerides[$cnt]['date'] = $date;

                    $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich(
                        $date
                    );
                    $deltaT = Time::deltaT($date);

                    // Calculate the ephemerids for the target
                    if ($this->_observationType['type'] == 'sun') {
                        $deltaT     = Time::deltaT($date);

                        $nutation = Time::nutation($deltaT);

                        $target->calculateEquatorialCoordinatesHighAccuracy($date, $nutation);
                        $target->calculateEphemerides(
                            $geo_coords,
                            $greenwichSiderialTime,
                            $deltaT
                        );
                    } elseif ($this->_observationType['type'] == 'planets') {
                        $deltaT     = Time::deltaT($date);

                        $target->calculateEquatorialCoordinates($date);
                        $target->calculateEphemerides(
                            $geo_coords,
                            $greenwichSiderialTime,
                            $deltaT
                        );
                    } else {
                        $target->calculateEphemerides(
                            $geo_coords,
                            $greenwichSiderialTime,
                            $deltaT
                        );
                    }

                    $nightephemerides = date_sun_info(
                        $date->getTimestamp(),
                        $location->latitude,
                        $location->longitude
                    );
                    $ephemerides[$cnt]['max_alt'] = trim(
                        $target->getMaxHeightAtNight()->convertToShortDegrees()
                    );
                    $ephemerides[$cnt]['max_alt_float'] = $target
                        ->getMaxHeightAtNight()->getCoordinate();
                    $ephemerides[$cnt]['transit'] = $target->getTransit()
                        ->timezone($this->_location->timezone)->format('H:i');
                    $ephemerides[$cnt]['rise'] = $target->getRising() ?
                        $target->getRising()->timezone($this->_location->timezone)
                        ->format('H:i') : '-';
                    $ephemerides[$cnt]['set'] = $target->getSetting() ?
                        $target->getSetting()->timezone($this->_location->timezone)
                        ->format('H:i') : '-';
                    $ephemerides[$cnt]['transitCarbon'] = $target->getTransit()
                        ->timezone($this->_location->timezone);
                    $ephemerides[$cnt]['riseCarbon'] = $target->getRising() ?
                        $target->getRising()->timezone($this->_location->timezone)
                        : '-';
                    $ephemerides[$cnt]['setCarbon'] = $target->getSetting() ?
                        $target->getSetting()->timezone($this->_location->timezone)
                        : '-';

                    $ephemerides[$cnt]['astronomical_twilight_end'] = is_bool(
                        $nightephemerides['astronomical_twilight_end']
                    ) ? null :
                        $date->copy()
                        ->setTimeFromTimeString(
                            date(
                                'H:i',
                                $nightephemerides['astronomical_twilight_end']
                            )
                        )->timezone($this->_location->timezone);

                    $ephemerides[$cnt]['astronomical_twilight_begin'] = is_bool(
                        $nightephemerides['astronomical_twilight_begin']
                    ) ? null :
                    $date->copy()
                        ->setTimeFromTimeString(
                            date(
                                'H:i',
                                $nightephemerides['astronomical_twilight_begin']
                            )
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

                    ++$cnt;
                }
            }

            // Setting the classes for the different colors
            $cnt = 0;
            foreach ($ephemerides as $ephem) {
                // Green if the max_alt does not change. This means that the
                // altitude is maximal
                if (($ephem['max_alt'] != '-'
                    && $ephemerides[($cnt + 1) % 24]['max_alt'] != '-')
                    && (abs($ephem['max_alt_float'] - $ephemerides[($cnt + 1) % 24]['max_alt_float']) <= 0.1
                    || abs($ephem['max_alt_float'] - $ephemerides[($cnt + 23) % 24]['max_alt_float']) <= 0.1)
                ) {
                    $ephemerides[$cnt]['max_alt_color'] = 'ephemeridesgreen';
                    $ephemerides[$cnt]['max_alt_popup']
                        = _i(
                            '%s reaches its highest altitude of the year',
                            $this->target_name
                        );
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
                        $ephemerides[$cnt]['transit_popup'] = _i(
                            '%s reaches its highest altitude during the astronomical night',
                            $this->target_name
                        );
                    } elseif ($ephem['nautical_twilight_end'] != null
                        && $time->between(
                            $ephem['nautical_twilight_begin'],
                            $ephem['nautical_twilight_end']
                        )
                    ) {
                        $ephemerides[$cnt]['transit_color'] = 'ephemeridesyellow';
                        $ephemerides[$cnt]['transit_popup'] = _i(
                            '%s reaches its highest altitude during the nautical twilight',
                            $this->target_name
                        );
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
                            $ephemerides[$cnt]['rise_popup'] = _i(
                                '%s is visible during the night',
                                $this->target_name
                            );
                            $ephemerides[$cnt]['rise_color'] = 'ephemeridesgreen';
                        } elseif ($ephem['nautical_twilight_end'] != null) {
                            $ephemerides[$cnt]['rise_popup'] = _i(
                                '%s is visible during the nautical twilight',
                                $this->target_name
                            );
                            $ephemerides[$cnt]['rise_color'] = 'ephemeridesyellow';
                        }
                    }
                    if ($ephem['riseCarbon'] != '-' && $ephem['setCarbon'] != '-') {
                        if ($ephem['astronomical_twilight_end'] != null
                            && $this->_checkNightHourMinutePeriodOverlap(
                                $ephem['riseCarbon'],
                                $ephem['setCarbon'],
                                $ephem['astronomical_twilight_end'],
                                $ephem['astronomical_twilight_begin']
                            )
                        ) {
                            $ephemerides[$cnt]['rise_popup'] = _i(
                                '%s is visible during the night',
                                $this->target_name
                            );
                            $ephemerides[$cnt]['rise_color'] = 'ephemeridesgreen';
                        } elseif ($ephem['nautical_twilight_end'] != null
                            && $this->_checkNightHourMinutePeriodOverlap(
                                $ephem['riseCarbon'],
                                $ephem['setCarbon'],
                                $ephem['nautical_twilight_end'],
                                $ephem['nautical_twilight_begin']
                            )
                        ) {
                            $ephemerides[$cnt]['rise_color'] = 'ephemeridesyellow';
                            $ephemerides[$cnt]['rise_popup'] = _i(
                                '%s is visible during the nautical twilight',
                                $this->target_name
                            );
                        }
                    } else {
                        $ephemerides[$cnt]['rise_popup'] = _i(
                            '%s is not visible during the night',
                            $this->target_name
                        );
                    }
                }

                ++$cnt;
            }

            $this->_ephemerides = $ephemerides;

            $collection = collect($ephemerides);

            $max_alt = $collection->max('max_alt_float');
            $filter  = $collection->filter(
                function ($value) use ($max_alt) {
                    if (abs($value['max_alt_float'] - $max_alt) < 0.1) {
                        return true;
                    }
                }
            );

            $months = $filter->keys();

            if ($months->min() == 0 && $months->max() == 23) {
                $missing = collect(range(0, 23))->diff($months);

                for ($i = 0; $i < $missing->min(); ++$i) {
                    $months[$i] += 24;
                }
            }
            $around = ($months->min()
                + ($months->max() - $months->min()) / 2) % 24 + 1;
            $from = $months->min() % 24 + 1;
            $to   = $months->max() % 24 + 1;

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
    private function _convertToMonth($number): string
    {
        $date = Carbon::now()->month($number / 2);

        return ($number % 2 ? _i('mid') : _i('begin'))
                . ' '
                . $date->isoFormat('MMMM');
    }

    /**
     * Checks if there is an overlap between the two given time periods.
     *
     * @param Carbon $firststart  the start of the first time interval
     * @param Carbon $firstend    the end of the first time interval
     * @param Carbon $secondstart the start of the second time interval
     * @param Carbon $secondend   the end of the second time interval
     *
     * @return bool true if the two time intervals overlap
     */
    private function _checkNightHourMinutePeriodOverlap(
        ?Carbon $firststart,
        ?Carbon $firstend,
        ?Carbon $secondstart,
        ?Carbon $secondend
    ) {
        if ($secondstart->lt($secondend)) {
            return ($firststart->gt($secondstart)
                 && $firststart->lt($secondend))
                 || ($firstend->gt($secondstart)
                 && $firstend->lt($secondend))
                 || ($firststart->lt($secondend)
                 && $firstend->gt($secondend))
                 || ($firststart->lt($secondstart)
                 && $firststart->gt($firstend))
                 || ($firstend->gt($secondend)
                 && $firststart->gt($firstend));
        } else {
            return $firststart->gt($secondstart)
                 || $firststart->lt($secondend)
                 || $firstend->gt($secondstart)
                 || $firstend->lt($secondend)
                 || ($firststart->lt($secondstart)
                 && $firstend->gt($secondend)
                 && $firststart->gt($firstend));
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
     * Returns the graph with the altitude of the target.
     *
     * @return string The altitude of the target
     */
    public function getAltitudeGraph()
    {
        if (Auth::guest()) {
            return '';
        }

        if (!Auth::user()->stdlocation) {
            return '';
        }
        if (!$this->_target) {
            $this->getRiseSetTransit();
        }

        if ($this->_location == null) {
            $this->_location = \App\Models\Location::where(
                'id',
                Auth::user()->stdlocation
            )->first();
        }
        $location = $this->_location;

        $geo_coords = new GeographicalCoordinates(
            $location->longitude,
            $location->latitude
        );

        $datestr      = Session::get('date');
        $date         = Carbon::createFromFormat('Y-m-d', $datestr);
        $date->hour   = 12;
        $date->minute = 0;
        $date->second = 0;
        $date->timezone($location->timezone);

        return $this->_target->altitudeGraph($geo_coords, $date);
    }

    /**
     * Returns the data from one catalog.
     *
     * @param string $catalogname The name of the catalog
     *
     * @return array the array with the collection with all the target information,
     *               array with constellations and array with types
     */
    public static function getCatalogData($catalogname): array
    {
        if (in_array(
            $catalogname,
            [
                _i('Comets'), _i('Planets'), _i('Sun'),
                _i('Moon Craters'), _i('Moon Mountains'),
                _i('Moon Other Feature'), _i('Moon Sea'), _i('Moon Valley'),
                _i('Planetary Moons'), _i('Asteroids'), _i('Dwarf Planets'),
            ]
        )
        ) {
            if ($catalogname == _i('Comets')) {
                $id = 'COMET';
            } elseif ($catalogname == _i('Planets')) {
                $id = 'PLANET';
            } elseif ($catalogname == _i('Sun')) {
                $id = 'SUN';
            } elseif ($catalogname == _i('Moon Craters')) {
                $id = 'CRATER';
            } elseif ($catalogname == _i('Moon Mountains')) {
                $id = 'MOUNTAIN';
            } elseif ($catalogname == _i('Moon Other Feature')) {
                $id = 'OTHER';
            } elseif ($catalogname == _i('Moon Sea')) {
                $id = 'SEA';
            } elseif ($catalogname == _i('Moon Valley')) {
                $id = 'VALLEY';
            } elseif ($catalogname == _i('Planetary Moons')) {
                $id = 'MOON';
            } elseif ($catalogname == _i('Asteroids')) {
                $id = 'ASTEROID';
            } elseif ($catalogname == _i('Dwarf Planets')) {
                $id = 'DWARF';
            }
            $allData = \App\Models\Target::where('target_type', $id)
                ->get('id')->pluck('id');
            $targetnames = \App\Models\TargetName::whereIn(
                'target_id',
                $allData
            )->get();
            $data = $targetnames->collect()->sortBy('altname', SORT_NATURAL);

            return [$data, new Collection(), new Collection()];
        }
        $allData = \App\Models\TargetName::with('target')->where('catalog', $catalogname)
            ->get()->collect()->sortBy('altname', SORT_NATURAL);
        $data = \App\Models\TargetName::where('catalog', $catalogname);

        $orig_data      = \App\Models\Target::whereIn('id', $data->get('target_id'))->get();
        $constellations = $orig_data->groupBy('constellation')->map(
            function ($constellation) {
                return $constellation->count();
            }
        )->toArray();
        ksort($constellations);

        $types = $orig_data->groupBy('target_type')->map(
            function ($target_type) {
                return $target_type->count();
            }
        )->toArray();
        ksort($types);

        return [$allData, $constellations, $types];
    }

    /**
     * Target can have more target_names.
     *
     * @return HasMany The eloquent relationship
     */
    public function target_names()
    {
        return $this->hasMany('App\Models\TargetName', 'target_id');
    }
}
