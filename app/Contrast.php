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

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
    public $contype = '';
    public $popup = '';
    public $prefMag = '';
    public $prefMagEasy = '';
    private $_LTC;
    private $_LTCSize = 24;
    private $_angleSize = 7;
    private $_angle = [
        -0.2255, 0.5563, 0.9859, 1.260,
        1.742, 2.083, 2.556,
    ];
    private $_magnifications;
    private $_magnificationsName;
    private $_fov;
    private $_initBB;
    private $_SBB1;
    private $_SBB2;
    private $_x;
    private $_xName;
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
        $this->_telescope = \App\Instrument::where(
            'id',
            Auth::user()->stdtelescope
        )->get()->first();

        $this->_location = \App\Location::where(
            'id',
            Auth::user()->stdlocation
        )->get()->first();

        $this->_prepareObjectsContrast();

        $this->_target = $target;

        $this->_calculateContrast();
        $this->_calcContrastAndVisibility();
    }

    /**
     * Speed up contrast calculations.
     *
     * @return None
     */
    private function _prepareObjectsContrast()
    {
        $this->_LTC = [
            [
                4, -0.3769, -1.8064, -2.3368, -2.4601,
                -2.5469, -2.5610, -2.5660,
            ],
            [
                5, -0.3315, -1.7747, -2.3337, -2.4608,
                -2.5465, -2.5607, -2.5658,
            ],
            [
                6, -0.2682, -1.7345, -2.3310, -2.4605,
                -2.5467, -2.5608, -2.5658,
            ],
            [
                7, -0.1982, -1.6851, -2.3140, -2.4572,
                -2.5481, -2.5615, -2.5665,
            ],
            [
                8, -0.1238, -1.6252, -2.2791, -2.4462,
                -2.5463, -2.5597, -2.5646,
            ],
            [
                9, -0.0424, -1.5529, -2.2297, -2.4214,
                -2.5343, -2.5501, -2.5552,
            ],
            [
                10, 0.0498, -1.4655, -2.1659, -2.3763,
                -2.5047, -2.5269, -2.5333,
            ],
            [
                11, 0.1596, -1.3581, -2.0810, -2.3036,
                -2.4499, -2.4823, -2.4937,
            ],
            [
                12, 0.2934, -1.2256, -1.9674, -2.1965,
                -2.3631, -2.4092, -2.4318,
            ],
            [
                13, 0.4557, -1.0673, -1.8186, -2.0531,
                -2.2445, -2.3083, -2.3491,
            ],
            [
                14, 0.6500, -0.8841, -1.6292, -1.8741,
                -2.0989, -2.1848, -2.2505,
            ],
            [
                15, 0.8808, -0.6687, -1.3967, -1.6611,
                -1.9284, -2.0411, -2.1375,
            ],
            [
                16, 1.1558, -0.3952, -1.1264, -1.4176,
                -1.7300, -1.8727, -2.0034,
            ],
            [
                17, 1.4822, -0.0419, -0.8243, -1.1475,
                -1.5021, -1.6768, -1.8420,
            ],
            [
                18, 1.8559, 0.3458, -0.4924, -0.8561,
                -1.2661, -1.4721, -1.6624,
            ],
            [
                19, 2.2669, 0.6960, -0.1315, -0.5510,
                -1.0562, -1.2892, -1.4827,
            ],
            [
                20, 2.6760, 1.0880, 0.2060, -0.3210,
                -0.8800, -1.1370, -1.3620,
            ],
            [
                21, 2.7766, 1.2065, 0.3467, -0.1377,
                -0.7361, -0.9964, -1.2439,
            ],
            [
                22, 2.9304, 1.3821, 0.5353, 0.0328,
                -0.5605, -0.8606, -1.1187,
            ],
            [
                23, 3.1634, 1.6107, 0.7708, 0.2531,
                -0.3895, -0.7030, -0.9681,
            ],
            [
                24, 3.4643, 1.9034, 1.0338, 0.4943,
                -0.2033, -0.5259, -0.8288,
            ],
            [
                25, 3.8211, 2.2564, 1.3265, 0.7605,
                0.0172, -0.2992, -0.6394,
            ],
            [
                26, 4.2210, 2.6320, 1.6990, 1.1320,
                0.2860, -0.0510, -0.4080,
            ],
            [
                27, 4.6100, 3.0660, 2.1320, 1.5850,
                0.6520, 0.2410, -0.1210,
            ],
        ];

        if (Auth::guest()) {
            $this->popup = _i('Contrast reserve can only be calculated when you are logged in...');
        } else {
            if (Auth::user()->stdlocation == 0) {
                $this->popup = _i('Contrast reserve can only be calculated when you have set a standard location...');
            } elseif (Auth::user()->stdtelescope == 0) {
                $this->popup = _i('Contrast reserve can only be calculated when you have set a standard instrument...');
            } else {
                // Check for eyepieces or a fixed magnification
                $instrument = $this->_telescope;

                if ($instrument->fd == null
                    && $instrument->fixedMagnification == null
                ) {
                    // We are not setting $magnifications
                    $this->_magnifications = [];
                } elseif ($instrument->fixedMagnification == 0) {
                    $eyepieces = \App\Eyepiece::where('user_id', Auth::user()->id)
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
                                    .' - '.$focalLengthEyepiece.'mm';
                                $this->_fov[] = 1.0 /
                                    ($instrument->diameter * $instrument->fd
                                    / $focalLengthEyepiece)
                                    * 60.0 * $eyepiece->apparentFOV;
                            }
                        } else {
                            $this->_magnifications[] = $instrument->diameter
                                * $instrument->fd / $eyepiece->focalLength;
                            $this->_magnificationsName[] = $eyepiece->name;
                            $this->_fov[] = 1.0 /
                                ($instrument->diameter * $instrument->fd
                                / $eyepiece->focalLength) * 60.0
                                * $eyepiece->apparentFOV;
                        }
                    }

                    $lenses = \App\Lens::where('user_id', Auth::user()->id)
                        ->get();
                    if (count($lenses) > 0) {
                        $origmagnifications = $this->_magnifications;
                        $origmagnificationsName = $this->_magnificationsName;
                        $origfov = $this->_fov;
                        foreach ($lenses as $lens) {
                            $name = $lens->name;
                            $factor = $lens->factor;
                            for ($i = 0; $i < count($origmagnifications); $i++) {
                                $magnifications[] = $origmagnifications[$i]
                                    * $factor;
                                $magnificationsName[] = $origmagnificationsName[$i]
                                    .', '.$name;
                                $fov[] = $origfov[$i] / $factor;
                            }
                        }
                        $this->_magnifications = $magnifications;
                        $this->_magnificationsName = $magnificationsName;
                        $this->_fov = $fov;
                    }
                } else {
                    $this->_magnifications[] = $instrument->fixedMagnification;
                    $this->_magnificationsName[] = '';
                    $this->_fov[] = '';
                }
                if (count($this->_magnifications) == 0) {
                    $this->popup = _i('Contrast reserve can only be calculated when the standard instrument has a fixed magnification or when there are eyepieces defined...');
                } else {
                    $location = $this->_location;

                    if (($location->limitingMagnitude < -900)
                        && ($location->skyBackground < -900)
                    ) {
                        $this->popup = _i('Contrast reserve can only be calculated when you have set a typical limiting magnitude or sky background for your standard location...');
                    } else {
                        $this->_initBB = $location->skyBackground;

                        $aperIn = $instrument->diameter / 25.4;

                        // Minimum useful magnification
                        $this->_SBB1 = $this->_initBB - (5 * log10(2.833 * $aperIn));
                        $this->_SBB2 = -2.5 * log10((2.833 * $aperIn) * (2.833 * $aperIn));
                    }
                }
            }
        }

        return $this->popup;
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
        $target = $this->_target;
        $minObjArcmin = $target->diam1 / 60.0;
        $maxObjArcmin = $target->diam2 / 60.0;

        if ($minObjArcmin > $maxObjArcmin) {
            $temp = $minObjArcmin;
            $minObjArcmin = $maxObjArcmin;
            $maxObjArcmin = $temp;
        }
        $maxLog = 37;

        // Log Object contrast
        $logObjContrast = -0.4 * ($target->SBObj - $this->_initBB);

        $bestLogContrastDiff = -$maxLog;
        $bestX = 0;

        // The preparations are finished, we can now start the calculations
        $mags = $this->_magnifications;
        $magsName = $this->_magnificationsName;
        $fovs = $this->_fov;

        if (count($mags) > 1) {
            $check = 0;
            $fovMax = 0;
            $fovMaxcnt = -1;

            for ($cnt = 0; $cnt < count($mags); $cnt++) {
                if ($fovs[$cnt] > $fovMax) {
                    $fovMaxcnt = $cnt;
                    $fovMax = $fovs[$cnt];
                }

                if ($fovs[$cnt] > $maxObjArcmin) {
                    $doCalc[] = 1;
                    $check = 1;
                } else {
                    $doCalc[] = 0;
                }
            }

            if ($check == 0) {
                $doCalc[$fovMaxcnt] = 1;
            }
        } else {
            $doCalc[0] = 1;
        }

        for ($cnt = 0; $cnt < count($mags); $cnt++) {
            if ($doCalc[$cnt] == 1) {
                $x = $mags[$cnt];
                $xName = $magsName[$cnt];

                $SBReduc = 5 * log10($x);
                $SBB = $this->_SBB1 + $SBReduc;
                $SBScopeAtX = $this->_SBB2 + $target->SBObj + $SBReduc;
                /* surface brightness of object + background brightness */
                $SBBBScopeAtX = $this->_SBB2
                    - (0.4 * $target->SBObj * $this->_initBB) + $SBReduc;

                /* 2 dimensional interpolation of LTC array */
                $ang = $x * $minObjArcmin;
                $logAng = log10($ang);
                $SB = $SBB;
                $I = 0;

                /* int of surface brightness */
                $intSB = (int) $SB;
                /* surface brightness index A */
                $SBIA = $intSB - 4;
                /* min index must be at least 0 */
                if ($SBIA < 0) {
                    $SBIA = 0;
                }
                /* max SBIA index cannot > 22 so that max SBIB <= 23 */
                if ($SBIA > $this->_LTCSize - 2) {
                    $SBIA = $this->_LTCSize - 2;
                }
                /* surface brightness index B */
                $SBIB = $SBIA + 1;

                while ($I < $this->_angleSize && $logAng > $this->_angle[$I++]);

                /* found 1st Angle[] value > LogAng, so back up 2 */
                $I -= 2;
                if ($I < 0) {
                    $I = 0;
                    $logAng = $this->_angle[0];
                }

                /* ie, if LogAng = 4 and Angle[I] = 3 and Angle[I+1] = 5,
                InterpAngle = .5, or .5 of the way between Angle[I] and Angle{I+1] */
                $interpAngle = ($logAng - $this->_angle[$I])
                    / ($this->_angle[$I + 1] - $this->_angle[$I]);
                /* add 1 to I because first entry in LTC is
                sky background brightness */
                $interpA = $this->_LTC[$SBIA][$I + 1]
                    + $interpAngle
                    * ($this->_LTC[$SBIA][$I + 2]
                    - $this->_LTC[$SBIA][$I + 1]);
                $interpB = $this->_LTC[$SBIB][$I + 1]
                    + $interpAngle
                    * ($this->_LTC[$SBIB][$I + 2]
                    - $this->_LTC[$SBIB][$I + 1]);
                if ($SB < $this->_LTC[0][0]) {
                    $SB = $this->_LTC[0][0];
                }
                if ($intSB >= $this->_LTC[$this->_LTCSize - 1][0]) {
                    $logThreshContrast = $interpB
                        + ($SB - $this->_LTC[$this->_LTCSize - 1][0])
                        * ($interpB - $interpA);
                } else {
                    $logThreshContrast = $interpA + ($SB - $intSB)
                        * ($interpB - $interpA);
                }

                if ($logThreshContrast > $maxLog) {
                    $logThreshContrast = $maxLog;
                } else {
                    if ($logThreshContrast < -$maxLog) {
                        $logThreshContrast = -$maxLog;
                    }
                }

                $logContrastDiff = $logObjContrast - $logThreshContrast;

                if ($logContrastDiff > $bestLogContrastDiff) {
                    $bestLogContrastDiff = $logContrastDiff;
                    $bestX = $x;
                    $bestXName = $xName;
                    $this->_xName = $bestXName;
                }
            }
        }
        $this->_x = $bestX;
        $this->_logContrastDiff = $bestLogContrastDiff;
    }

    private function _calcContrastAndVisibility()
    {
        $this->contrast = '-';
        $this->prefMag = '-';
        $this->prefMagEasy = '-';

        $showname = $this->_target->name;

        if (Auth::guest()) {
            return;
        }
        if ($this->_target->mag == null) {
            $this->popup = _i('Contrast reserve can only be calculated when the object has a known magnitude');

            return;
        } else {
            $diam1 = $this->_target->diam1 / 60.0;
            if ($diam1 == 0) {
                $this->popup = _i('Contrast reserve can only be calculated when the object has a known diameter');
                $this->contrast = '-';
            } else {
                $diam2 = $this->_target->diam2 / 60.0;
                if ($diam2 == 0) {
                    $diam2 = $diam1;
                }
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
                $this->contrast = sprintf('%.2f', $this->_logContrastDiff);
                $this->prefMagEasy = sprintf('%d', $this->_x).'x';
                if ($this->_xName == '') {
                    $this->prefMag = sprintf('%d', $this->_x).'x';
                } else {
                    $this->prefMag = sprintf('%d', $this->_x).'x'
                        .' - '.$this->_xName;
                }
            }
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
