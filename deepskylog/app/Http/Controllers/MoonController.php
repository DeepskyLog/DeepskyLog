<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\ObservingList;
use App\Services\Moon\LunarTerminator;
use App\Services\Moon\MoonDiskRenderer;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The Moon in a night (/moon): its disk with the terminator, phase,
 * colongitude and libration, the lunar features at the terminator, and the
 * coming nights, at one of the observer's locations (else
 * Location::forEphemerides()), for the night of the ephemeris date or ?night=,
 * at the moment the Moon is highest or ?time= (local, before noon the morning
 * after).
 */
class MoonController extends Controller
{
    /** The nights in the table of the coming nights. */
    public const UPCOMING_NIGHTS = 14;

    /** The features of each coming night. */
    public const UPCOMING_FEATURES = 4;

    /** The features of the table shown before "Show all". */
    public const SHOWN_FEATURES = 20;

    /** The features numbered on the disk. */
    public const MARKERS = 12;

    public function show(Request $request): View
    {
        $context = $this->context($request);
        if ($context === null) {
            return view('moon.show', ['moon' => null]);
        }
        $moon        = LunarTerminator::nightAt($context['location'], $context['night'], $context['timezone'], $context['at']);
        $features    = LunarTerminator::features($moon['ephemeris']);
        $orientation = in_array($request->query('view'), MoonDiskRenderer::ORIENTATIONS, true) ? $request->query('view') : 'north';

        return view('moon.show', [
            'moon'        => $moon,
            'features'    => $features,
            'disk'        => (new MoonDiskRenderer($moon['ephemeris'], $orientation))->svg(array_slice($features, 0, self::MARKERS)),
            'orientation' => $orientation,
            'upcoming'    => LunarTerminator::upcoming($context['location'], Carbon::parse($context['night'])->addDay()->toDateString(), $context['timezone'], self::UPCOMING_NIGHTS, self::UPCOMING_FEATURES),
            'location'    => $context['location'],
            'own'         => $context['own'],
            'locations'   => $this->locationOptions(),
            'time'        => $context['at'] === null ? null : Carbon::createFromTimestamp($context['at'], $context['timezone'])->format('H:i'),
        ]);
    }

    /**
     * Make an observing list of the features at the terminator, each with the
     * moment of the Moon on the page.
     */
    public function storeList(Request $request): RedirectResponse
    {
        $this->authorize('create', ObservingList::class);

        $context = $this->context($request);
        $moon    = $context ? LunarTerminator::nightAt($context['location'], $context['night'], $context['timezone'], $context['at']) : null;
        $names   = $moon ? array_column(LunarTerminator::features($moon['ephemeris']), 'name') : [];
        if ($names === []) {
            return redirect()->route('moon.show')->with('error', __('There are no lunar features at the terminator to put on a list.'));
        }

        $date = Carbon::parse($context['night'])->locale(app()->getLocale())->isoFormat('LL');
        $list = ObservingList::createWithObjects(
            $request->user(),
            __('Moon terminator, night of :date', ['date' => $date]),
            $names,
            __('The lunar features at the terminator, colongitude :colongitude°.', ['colongitude' => number_format($moon['ephemeris']['colongitude'], 1)]),
            false,
            collect($names)->mapWithKeys(fn (string $name) => [$name => ['at' => $moon['moment']]])->all(),
            [
                'night'       => $context['night'],
                'timezone'    => $context['timezone'],
                'location_id' => $context['own'] ? $context['location']->id : null,
            ],
        );

        return redirect()->route('observing-list.show', $list)
            ->with('success', __('Observing list created with :count object(s).', ['count' => $list->items()->count()]));
    }

    /**
     * The location, whether it is the observer's own, its time zone, the
     * night and the chosen moment (null: the Moon at its highest), or null
     * when there is no location with coordinates.
     *
     * @return array{location: Location, own: bool, timezone: string, night: string, at: ?int}|null
     */
    private function context(Request $request): ?array
    {
        $location = null;
        if (Auth::check() && $request->filled('location')) {
            $location = Location::where('user_id', Auth::id())->find((int) $request->input('location'));
        }
        $location ??= Location::forEphemerides(Auth::user());
        if (! $location || ! $location->hasValidCoordinates()) {
            return null;
        }

        $timezone = EphemerisDate::timezone($location);
        $night    = (string) $request->input('night');
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $night) || ! checkdate((int) substr($night, 5, 2), (int) substr($night, 8, 2), (int) substr($night, 0, 4))) {
            $night = EphemerisDate::current($timezone);
        }

        $at   = null;
        $time = (string) $request->input('time');
        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time, $match)) {
            $moment = Carbon::parse($night.' '.$time, $timezone);
            // a time before noon is the morning after
            if ((int) $match[1] < 12) {
                $moment->addDay();
            }
            $at = $moment->getTimestamp();
        }

        return [
            'location' => $location,
            'own'      => Auth::check() && (int) $location->user_id === (int) Auth::id(),
            'timezone' => $timezone,
            'night'    => $night,
            'at'       => $at,
        ];
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
