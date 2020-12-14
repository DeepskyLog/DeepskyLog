<?php

/**
 * Contrast class.
 *
 * PHP Version 7
 *
 * @category Targets
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * Contrast class.
 *
 * @category Targets
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Contrast extends Model
{
    private $_target;
    public $contype     = '';
    public $popup       = '';
    public $prefMag     = '';
    public $prefMagEasy = '';
    private $_magnifications;
    private $_magnificationsName;
    private $_x;
    private $_logContrastDiff;
    private $_location;
    private $_telescope;
    public $contrast;

    /**
     * Create a new contrast object, starting from a target.
     *
     * @param Target $target the target for which we want to calculate the contrast
     */
    public function __construct(Target $target)
    {
        $this->_telescope = \App\Models\Instrument::where(
            'id',
            Auth::user()->stdtelescope
        )->get()->first();

        $this->_location = \App\Models\Location::where(
            'id',
            Auth::user()->stdlocation
        )->get()->first();

        $this->_target = $target;

        $this->_calculateContrast();
    }

    /**
     * This function calculates the contrast of the object. Contrast Difference,
     * minimum usefull magnification, and Optimum detection magnification are
     * calculated.
     *  If the contrast difference is < 0, the object is not visible.
     *         contrast difference < -0.2 : Not visible - Dark Gray : 777777
     *         -0.2 < contrast diff < 0.1 : Fraglich - Gray : 999999
     *        0.10 < contrast diff < 0.35 : Schwierig - Red : CC0000
     *         0.35 < contrast diff < 0.5 : Mittelschwer - Orange : FF6600
     *            0.50 < contr diff < 1.0 : Leicht Sichtbar - Dark green : 339900
     *               1.00 < contrast diff : Leicht Sichtbar - Light green : 66FF00.
     *
     * @return None
     */
    private function _calculateContrast()
    {
        $this->contrast = '-';

        if (Auth::guest()) {
            $this->popup = _i('Contrast reserve can only be calculated when you are logged in...');
            return;
        } else {
            if (Auth::user()->stdlocation == 0) {
                $this->popup = _i('Contrast reserve can only be calculated when you have set a standard location...');
                return;
            } elseif (Auth::user()->stdtelescope == 0) {
                $this->popup = _i('Contrast reserve can only be calculated when you have set a standard instrument...');
                return;
            } else {
                // Check for eyepieces or a fixed magnification
                $instrument = $this->_telescope;

                if ($instrument->fd == null
                    && $instrument->fixedMagnification == null
                ) {
                    // We are not setting $magnifications
                    $this->_magnifications = [];
                } elseif ($instrument->fixedMagnification == 0) {
                    $eyepieces = \App\Models\Eyepiece::where('user_id', Auth::user()->id)->where('active', 1)
                        ->get();
                    foreach ($eyepieces as $eyepiece) {
                        if ($eyepiece->maxFocalLength != null) {
                            $fRange = $eyepiece->maxFocalLength
                                - $eyepiece->focalLength;
                            for ($i = 0; $i < 5; $i++) {
                                $focalLengthEyepiece = $eyepiece->focalLength
                                    + $i * $fRange / 5.0;
                                $this->_magnifications[] = $instrument->diameter
                                    * $instrument->fd / $focalLengthEyepiece;
                                $this->_magnificationsName[] = $eyepiece->name
                                    . ' - ' . $focalLengthEyepiece . 'mm';
                            }
                        } else {
                            $this->_magnifications[] = $instrument->diameter
                                * $instrument->fd / $eyepiece->focalLength;
                            $this->_magnificationsName[] = $eyepiece->name;
                        }
                    }
                    $lenses = \App\Models\Lens::where('user_id', Auth::user()->id)
                        ->get();
                    if (count($lenses) > 0) {
                        $origmagnifications     = $this->_magnifications;
                        $origmagnificationsName = $this->_magnificationsName;
                        foreach ($lenses as $lens) {
                            $name   = $lens->name;
                            $factor = $lens->factor;
                            for ($i = 0; $i < count($origmagnifications); $i++) {
                                array_push($this->_magnifications, $origmagnifications[$i]
                                    * $factor);
                                array_push($this->_magnificationsName, $origmagnificationsName[$i]
                                    . ', ' . $name);
                            }
                        }
                    }
                } else {
                    $this->_magnifications[]     = $instrument->fixedMagnification;
                    $this->_magnificationsName[] = '';
                    $this->_fov[]                = '';
                }
                if (count($this->_magnifications) == 0) {
                    $this->popup = _i('Contrast reserve can only be calculated when the standard instrument has a fixed magnification or when there are eyepieces defined...');
                    return;
                } else {
                    $location = $this->_location;

                    if ((!$location->limitingMagnitude)
                        && (!$location->skyBackground)
                    ) {
                        $this->popup = _i('Contrast reserve can only be calculated when you have set a typical limiting magnitude or sky background for your standard location...');
                        return;
                    }
                }
            }
        }

        $target       = $this->_target;
        $extTarget    = new \deepskylog\AstronomyLibrary\Targets\Target();
        if (!$target->diam1) {
            $this->popup    = _i('Contrast reserve can only be calculated when the object has a known diameter');
            return;
        }
        $extTarget->setDiameter($target->diam1, $target->diam2);
        if ($target->mag == null) {
            $this->popup = _i('Contrast reserve can only be calculated when the object has a known magnitude');
            return;
        }
        $extTarget->setMagnitude($target->mag);
        if ($target->SBObj) {
            $this->_x               = $extTarget->calculateBestMagnification($target->SBObj, $this->_location->skyBackground, $this->_telescope->diameter, $this->_magnifications);
            $this->_logContrastDiff = $extTarget->calculateContrastReserve($target->SBObj, $this->_location->skyBackground, $this->_telescope->diameter, $this->_x);
        }

        $this->prefMag     = '-';
        $this->prefMagEasy = '-';

        $showname = $this->_target->target_name;

        if ($this->_logContrastDiff < -0.2) {
            $this->popup = sprintf(
                _i('%s is not visible from %s with your %s'),
                $showname,
                $this->_location->name,
                $this->_telescope->name
            );
        } elseif ($this->_logContrastDiff < 0.1) {
            $this->popup = sprintf(
                _i('Visibility of %s is questionable from %s with your %s'),
                $showname,
                $this->_location->name,
                $this->_telescope->name
            );
        } elseif ($this->_logContrastDiff < 0.35) {
            $this->popup = sprintf(
                _i('%s is difficult to see from %s with your %s'),
                $showname,
                $this->_location->name,
                $this->_telescope->name
            );
        } elseif ($this->_logContrastDiff < 0.5) {
            $this->popup = sprintf(
                _i('%s is quite difficult to see from %s with your %s'),
                $showname,
                $this->_location->name,
                $this->_telescope->name
            );
        } elseif ($this->_logContrastDiff < 1.0) {
            $this->popup = sprintf(
                _i('%s is easy to see from %s with your %s'),
                $showname,
                $this->_location->name,
                $this->_telescope->name
            );
        } else {
            $this->popup = sprintf(
                _i('%s is very easy to see from %s with your %s'),
                $showname,
                $this->_location->name,
                $this->_telescope->name
            );
        }
        $this->contrast    = sprintf('%.2f', $this->_logContrastDiff);
        $this->prefMagEasy = sprintf('%d', $this->_x) . 'x';
        if (array_search($this->_x, $this->_magnifications)) {
            $this->prefMag = sprintf('%d', $this->_x) . 'x'
                . ' - ' . $this->_magnificationsName[array_search($this->_x, $this->_magnifications)];
        } else {
            $this->prefMag = sprintf('%d', $this->_x) . 'x';
        }
        if ($this->contrast == '-') {
            $this->contype = '';
        } elseif ($this->contrast < -0.2) {
            $this->contype = 'typeNotVisible';
        } elseif ($this->contrast < 0.1) {
            $this->contype = 'typeQuestionable';
        } elseif ($this->contrast < 0.35) {
            $this->contype = 'typeDifficult';
        } elseif ($this->contrast < 0.5) {
            $this->contype = 'typeQuiteDifficult';
        } elseif ($this->contrast < 1.0) {
            $this->contype = 'typeEasy';
        } else {
            $this->contype = 'typeVeryEasy';
        }
    }
}
