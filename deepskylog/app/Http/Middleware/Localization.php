<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user() && Auth::user()->language != '') {
            App::setLocale(Auth::user()->language);
            Carbon::setLocale(Auth::user()->language);
        } elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
            Carbon::setLocale(Session::get('locale'));
        }

        return $next($request);
    }
}
