<?php

/**
 * DeepskyLog middleware. Does some checks and adds a flash message if needed.
 *
 * PHP Version 7
 *
 * @category Common
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * DeepskyLog middleware. Does some checks and adds a flash message if needed.
 *
 * @category Common
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class DeepskyLogChecks
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request The request to handle
     * @param \Closure                 $next    the next page / middleware to use
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::guest()) {
            // Check if the observer has set a country of residence.
            if (Auth::user()->country == '') {
                flash()->warning(
                    _i(
                        'Your country of residence is not set. Please set it in the observer settings.'
                    )
                );
            }

            // TODO: Check if a standard location is set

            // TODO: Check if a standard instrument is set
        }

        return $next($request);
    }
}
