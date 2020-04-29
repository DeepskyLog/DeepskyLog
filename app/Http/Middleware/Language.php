<?php
/**
 * Language middleware.
 *
 * PHP Version 7
 *
 * @category Language
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Middleware;

use Closure;
use deepskylog\LaravelGettext\Facades\LaravelGettext;
use Illuminate\Support\Facades\Auth;

/**
 * Language middleware.
 *
 * @category Lenses
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Language
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
        if (! Auth::guest()) {
            LaravelGettext::setLocale(Auth::user()->language);
        }

        return $next($request);
    }
}
