<?php

namespace App\Http\Controllers;

use App\Services\Weather\NightForecast;
use App\Services\Weather\SiteComparison;
use Illuminate\Http\Request;

/**
 * Which of my sites tonight? (App\Services\Weather\SiteComparison): the
 * observer's locations compared for the coming nights.
 */
class BestSiteController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $mode   = in_array($request->query('mode'), SiteComparison::MODES, true) ? $request->query('mode') : SiteComparison::MODE_DEEPSKY;
        $radius = in_array((int) $request->query('radius'), SiteComparison::RADII, true) ? (int) $request->query('radius') : null;
        $nights = NightForecast::enabled() ? SiteComparison::nights($user) : [];
        $night  = in_array($request->query('night'), $nights, true) ? $request->query('night') : ($nights[0] ?? null);

        // The forecasts not cached yet are fetched after the response
        $sites = $nights === [] ? [] : SiteComparison::compare($user, $nights, $mode, false, $radius);

        return view('location.best-site', [
            'enabled' => NightForecast::enabled(),
            'home'    => SiteComparison::home($user),
            'mode'    => $mode,
            'radius'  => $radius,
            'nights'  => $nights,
            'night'   => $night,
            'sites'   => $sites,
            'ranked'  => $night ? SiteComparison::ranked($sites, $night) : [],
            'pending' => count(array_filter($sites, fn (array $site) => $site['nights'] === null)),
        ]);
    }
}
