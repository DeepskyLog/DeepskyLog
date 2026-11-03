<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\Planets\MarsFeatures;
use App\Services\Planets\MarsVenus;
use App\Services\Planets\PlanetDisk;
use App\Services\Planets\PlanetDiskRenderer;
use App\Services\Planets\PlanetMoonsRenderer;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Mars and Venus in a night (/mars-venus): when each can be observed from
 * one of the observer's locations (else Location::forEphemerides()) in the
 * night of the ephemeris date or ?night=, each disk drawn at the moment the
 * planet is highest or at ?time= (local, before noon the morning after),
 * the central meridian of Mars through the night, the coming crossings of
 * the feature ?feature= (a key of MarsFeatures::FEATURES), the coming weeks
 * and the next opposition of Mars and the next phases of Venus.
 */
class MarsVenusController extends Controller
{
    public function show(Request $request): View
    {
        $location = null;
        if (Auth::check() && $request->filled('location')) {
            $location = Location::where('user_id', Auth::id())->find((int) $request->input('location'));
        }
        $location ??= Location::forEphemerides(Auth::user());
        if (! $location || ! $location->hasValidCoordinates()) {
            return view('mars-venus.show', ['night' => null]);
        }

        $timezone = EphemerisDate::timezone($location);
        $date     = (string) $request->input('night');
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || ! checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
            $date = EphemerisDate::current($timezone);
        }
        $night = MarsVenus::nightAt($location, $date, $timezone);

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
        $feature     = array_key_exists((string) $request->query('feature'), MarsFeatures::FEATURES) ? (string) $request->query('feature') : null;

        $planets = [];
        foreach (PlanetDisk::PLANETS as $planet) {
            // At the chosen moment, else when the planet is highest, else at midnight
            $moment           = $at ?? $night['planets'][$planet]['best'] ?? Carbon::parse($date . ' 12:00', $timezone)->addHours(12)->getTimestamp();
            $disk             = PlanetDisk::at($planet, $moment);
            $planets[$planet] = [
                'disk'   => $disk,
                'svg'    => (new PlanetDiskRenderer($disk, $orientation))->svg(),
                'facing' => $planet === 'mars' ? MarsFeatures::facing($disk) : [],
            ];
        }

        return view('mars-venus.show', [
            'night'       => $night,
            'planets'     => $planets,
            'hours'       => MarsVenus::meridianHours($night),
            'feature'     => $feature,
            'transits'    => $feature === null ? [] : MarsVenus::featureTransits($location, $feature, $date, $timezone),
            'weeks'       => MarsVenus::weeks($date),
            'events'      => MarsVenus::events($date),
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
