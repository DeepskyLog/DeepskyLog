<?php
/**
 * The contrast class calculates the contrast and magnification of a certain object,
 * with a certain instrument, under a certain sky.
 *
 * PHP Version 7
 *
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
global $inIndex;

if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
}

/**
 * The contrast class calculates the contrast and magnification of a certain object,
 * with a certain instrument, under a certain sky.
 *
 * PHP Version 7
 *
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
class Contrast
{
    /**
     * This function calculates the contrast of the object.
     *
     * @param float $objMag       The magnitude of the object.
     * @param float $SBObj        The surface brightness of the object.
     * @param float $minObjArcmin The size of the object in arcminutes.
     * @param float $maxObjArcmin The size of the object in arcminutes.
     *
     * @return array Array with (Contrast Difference, minimum usefull magnification,
     *               Optimum detection magnification).
     *               If the contrast difference is < 0, the object is not visible.
     *               contrast difference < -0.2 : Not visible - Dark Gray : 777777
     *               -0.2 < contrast diff < 0.1 : Fraglich - Gray : 999999
     *               0.10 < contrast diff < 0.35 : Schwierig - Red : CC0000
     *               0.35 < contrast diff < 0.5 : Mittelschwer - Orange : FF6600
     *               0.50 < contr diff < 1.0 : Leicht Sichtbar - Dark green : 339900
     *               1.00 < contrast diff : Leicht Sichtbar - Light green : 66FF00
     */
    public function calculateContrast($objMag, $SBObj, $minObjArcmin, $maxObjArcmin)
    {
        global $objObject;

        if ($minObjArcmin > $maxObjArcmin) {
            $temp = $minObjArcmin;
            $minObjArcmin = $maxObjArcmin;
            $maxObjArcmin = $temp;
        }
        $maxLog = 37;
        $maxX = 1000;

        // Log Object contrast
        $logObjContrast = -0.4 * ($SBObj - $_SESSION['initBB']);

        $bestLogContrastDiff = - $maxLog;
        $bestX = 0;

        // The preparations are finished, we can now start the calculations
        $mags = $_SESSION['magnifications'];
        $magsName = $_SESSION['magnificationsName'];
        $fovs = $_SESSION['fov'];

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
                $SBB = $_SESSION['SBB1'] + $SBReduc;
                $SBScopeAtX = $_SESSION['SBB2']  + $SBObj + $SBReduc;
                /* surface brightness of object + background brightness */
                $SBBBScopeAtX = $_SESSION['SBB2']
                    - (0.4 * $SBObj * $_SESSION['initBB']) + $SBReduc;

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
                if ($SBIA > $_SESSION['LTCSize'] - 2) {
                    $SBIA = $_SESSION['LTCSize'] - 2;
                }
                /* surface brightness index B */
                $SBIB = $SBIA + 1;

                while ($I < $_SESSION['angleSize'] && $logAng > $_SESSION['angle'][$I++])
                ;

                /* found 1st Angle[] value > LogAng, so back up 2 */
                $I -= 2;
                if ($I < 0) {
                    $I = 0;
                    $logAng = $_SESSION['angle'][0];
                }

                /* ie, if LogAng = 4 and Angle[I] = 3 and Angle[I+1] = 5,
                InterpAngle = .5, or .5 of the way between Angle[I] and Angle{I+1] */
                $interpAngle = ($logAng - $_SESSION['angle'][$I])
                    / ($_SESSION['angle'][$I + 1] - $_SESSION['angle'][$I]);
                /* add 1 to I because first entry in LTC is
                sky background brightness */
                $interpA = $_SESSION['LTC'][$SBIA][$I + 1]
                    + $interpAngle
                    * ($_SESSION['LTC'][$SBIA][$I + 2]
                    - $_SESSION['LTC'][$SBIA][$I + 1]);
                $interpB = $_SESSION['LTC'][$SBIB][$I + 1]
                    + $interpAngle
                    * ($_SESSION['LTC'][$SBIB][$I + 2]
                    - $_SESSION['LTC'][$SBIB][$I + 1]);
                if ($SB<$_SESSION['LTC'][0][0]) {
                    $SB = $_SESSION['LTC'][0][0];
                }
                if ($intSB >= $_SESSION['LTC'][$_SESSION['LTCSize'] - 1][0]) {
                    $logThreshContrast = $interpB
                        + ($SB - $_SESSION['LTC'][$_SESSION['LTCSize'] - 1][0])
                        * ($interpB - $interpA);
                } else {
                    $logThreshContrast = $interpA + ($SB - $intSB)
                        * ($interpB - $interpA);
                }

                if ($logThreshContrast > $maxLog) {
                    $logThreshContrast = $maxLog;
                } else {
                    if ($logThreshContrast < - $maxLog) {
                        $logThreshContrast = - $maxLog;
                    }
                }

                $logContrastDiff = $logObjContrast - $logThreshContrast;

                if ($logContrastDiff > $bestLogContrastDiff) {
                    $bestLogContrastDiff = $logContrastDiff;
                    $bestX = $x;
                    $bestXName = $xName;
                }
            }
        }
        $x = $bestX;
        $xName = $bestXName;
        $logContrastDiff = $bestLogContrastDiff;
        return array($logContrastDiff, $x, $xName);
    }

    /**
     * This function calculates the limiting magnitude if the sqm value is given.
     *
     * @param float $initBB The sqm value.
     *
     * @return float The limiting magnitude.
     */
    public function calculateLimitingMagnitudeFromSkyBackground($initBB)
    {
        return (7.97 - 5 * log10(1 + pow(10, 4.316 - $initBB / 5.0)));
    }

    /**
     * This function calculates the sqm if the limiting magnitude is given.
     *
     * @param float $limMag The limiting magnitude.
     *
     * @return float The sqm value.
     */
    public function calculateSkyBackgroundFromLimitingMagnitude($limMag)
    {
        return ((21.58 - 5 * log10(pow(10, (1.586 - $limMag / 5.0)) - 1.0)));
    }

    /**
     * This function calculates the bortle scale if the sqm value is given.
     *
     * @param float $sqm The sqm value.
     *
     * @return integer The bortle scale.
     */
    public function calculateBortleFromSQM($sqm)
    {
        if ($sqm <= 17.5) {
            return 9;
        } else if ($sqm <= 18.0) {
            return 8;
        } else if ($sqm <= 18.5) {
            return 7;
        } else if ($sqm <= 19.1) {
            return 6;
        } else if ($sqm <= 20.4) {
            return 5;
        } else if ($sqm <= 21.3) {
            return 4;
        } else if ($sqm <= 21.5) {
            return 3;
        } else if ($sqm <= 21.7) {
            return 2;
        } else {
            return 1;
        }
    }
}
?>
