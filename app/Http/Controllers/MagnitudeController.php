<?php

/**
 * Magnitude Controller.
 *
 * PHP Version 7
 *
 * @category Magnitude
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use deepskylog\AstronomyLibrary\Magnitude;
use Illuminate\Support\Facades\Auth;

/**
 * Magnitude Controller.
 *
 * PHP Version 7
 *
 * @category Magnitude
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class MagnitudeController extends Controller
{
    /**
     * Returns the sqm if the nelm is given.
     *
     * @param float $nelm The Naked Eye Limiting Magnitude
     *
     * @return \Illuminate\Http\Response
     */
    public function nelmToSqmJson(float $nelm): string
    {
        if (Auth::user()) {
            $fstOffset = Auth::user()->fstOffset;
        } else {
            $fstOffset = 0.0;
        }

        return '{"sqm": '.Magnitude::nelmToSqm($nelm, $fstOffset).'}';
    }

    /**
     * Returns the bortle scale if the nelm is given.
     *
     * @param float $nelm The Naked Eye Limiting Magnitude
     *
     * @return \Illuminate\Http\Response
     */
    public function nelmToBortleJson(float $nelm): string
    {
        return '{"bortle": '.Magnitude::nelmToBortle($nelm).'}';
    }

    /**
     * Returns the nelm if the sqm is given.
     *
     * @param float $sqm The sqm value
     *
     * @return \Illuminate\Http\Response
     */
    public function sqmToNelmJson(float $sqm): string
    {
        if (Auth::user()) {
            $fstOffset = Auth::user()->fstOffset;
        } else {
            $fstOffset = 0.0;
        }

        return '{"nelm": '.Magnitude::sqmToNelm($sqm, $fstOffset).'}';
    }

    /**
     * Returns the bortle scale if the sqm is given.
     *
     * @param float $sqm The Nsqm value
     *
     * @return \Illuminate\Http\Response
     */
    public function sqmToBortleJson(float $sqm): string
    {
        return '{"bortle": '.Magnitude::sqmToBortle($sqm).'}';
    }

    /**
     * Returns the nelm if the bortle scale is given.
     *
     * @param int $bortle The bortle scale
     *
     * @return \Illuminate\Http\Response
     */
    public function bortleToNelmJson(int $bortle): string
    {
        if (Auth::user()) {
            $fstOffset = Auth::user()->fstOffset;
        } else {
            $fstOffset = 0.0;
        }

        return '{"nelm": '.Magnitude::bortleToNelm($bortle, $fstOffset).'}';
    }

    /**
     * Returns the sqm if the bortle scale is given.
     *
     * @param int $bortle The bortle scale
     *
     * @return \Illuminate\Http\Response
     */
    public function bortleTosqmJson(int $bortle): string
    {
        return '{"sqm": '.Magnitude::bortleToSqm($bortle).'}';
    }
}
