<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\Planets\PlanetMoonEvents;
use App\Services\Planets\PlanetMoons;
use App\Services\Planets\PlanetMoonsRenderer;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The moons of Jupiter and Saturn in a night (/jupiter-saturn): the events
 * of PlanetMoonEvents that can be seen from one of the observer's locations
 * (else Location::forEphemerides()) in the night of the ephemeris date or
 * ?night=, each planet with its moons drawn at the moment it is highest or
 * at ?time= (local, before noon the morning after), and the coming nights.
 */
class PlanetMoonsController extends Controller
{
    /** The nights in the table of the coming nights. */
    public const UPCOMING_NIGHTS = 14;

    public function show(Request $request): View
    {
        $location = null;
        if (Auth::check() && $request->filled('location')) {
            $location = Location::where('user_id', Auth::id())->find((int) $request->input('location'));
        }
        $location ??= Location::forEphemerides(Auth::user());
        if (! $location || ! $location->hasValidCoordinates()) {
            return view('planet-moons.show', ['night' => null]);
        }

        $timezone = EphemerisDate::timezone($location);
        $date     = (string) $request->input('night');
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || ! checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
            $date = EphemerisDate::current($timezone);
        }
        $night = PlanetMoonEvents::nightAt($location, $date, $timezone);

        $at   = null;
        $time = (string) $request->input('time');
        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time, $match)) {
            $moment = Carbon::parse($date . ' ' . $time, $timezone);
            // a time before noon is the morning after
            if ((int) $match[1] < 12) {
                $moment->addDay();
            }
            $at = $moment->getTimestamp();
        }
        $orientation = in_array($request->query('view'), PlanetMoonsRenderer::ORIENTATIONS, true) ? $request->query('view') : 'north';

        $views = [];
        foreach (['jupiter', 'saturn'] as $planet) {
            // At the chosen moment, else when the planet is highest in the dark, else at midnight
            $moment         = $at ?? $night['planets'][$planet]['best'] ?? Carbon::parse($date . ' 12:00', $timezone)->addHours(12)->getTimestamp();
            $positions      = $planet === 'jupiter' ? PlanetMoons::jupiter($moment) : PlanetMoons::saturn($moment);
            $grs            = $planet === 'jupiter' ? PlanetMoonEvents::grsLongitude($moment) : null;
            $views[$planet] = [
                'moment'   => $moment,
                'svg'      => (new PlanetMoonsRenderer($planet, $positions, $orientation, $grs))->svg(),
                'close_up' => PlanetMoonsRenderer::closeUp($planet, $positions, $orientation, $grs)->svg(),
            ];
        }

        return view('planet-moons.show', [
            'night'       => $night,
            'views'       => $views,
            'upcoming'    => PlanetMoonEvents::upcoming($location, Carbon::parse($date)->addDay()->toDateString(), $timezone, self::UPCOMING_NIGHTS),
            'location'    => $location,
            'own'         => Auth::check() && (int) $location->user_id === (int) Auth::id(),
            'locations'   => $this->locationOptions(),
            'time'        => $at === null ? null : Carbon::createFromTimestamp($at, $timezone)->format('H:i'),
            'orientation' => $orientation,
        ]);
    }

    /**
     * The observer's active locations with coordinates.
     *
     * @return array<int, string>
     */
    private function locationOptions(): array
    {
        if (! Auth::check()) {
            return [];
        }

        return Location::where('user_id', Auth::id())
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'latitude', 'longitude'])
            ->filter(fn (Location $location) => $location->hasValidCoordinates())
            ->mapWithKeys(fn (Location $location) => [(int) $location->id => html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')])
            ->all();
    }
}
