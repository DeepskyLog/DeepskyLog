<?php

namespace App\Livewire;

use App\Models\ObservingList;
use App\Services\EquipmentGain;
use App\Services\RevisitObjects;
use App\Services\TripPlanner as Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * The trip planner, "What could I see from…?" (/observing-lists/trip): a
 * place picked on the map, the nights of the trip and one of the observer's
 * instruments give the objects that are new for them there
 * (App\Services\TripPlanner), with a button that makes them observing lists
 * with times per night. Picking a place proposes its time zone and sky; the
 * values are kept in the query string and only calculated with
 * calculate(), so a shared link (the starparty page links here) calculates
 * at once. The result is calculated on the queue; the page polls until it
 * is there.
 */
class TripPlanner extends Component
{
    #[Url]
    public string $lat = '';

    #[Url]
    public string $lon = '';

    #[Url]
    public string $place = '';

    #[Url]
    public string $sqm = '';

    #[Url(as: 'tz')]
    public string $timezone = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $until = '';

    /** One of the observer's instruments; empty for the standard telescope. */
    #[Url]
    public string $instrument = '';

    /** One of the observer's locations; empty for the standard location. */
    #[Url]
    public string $home = '';

    /** Trip::LOW, Trip::FAINTER or empty for both. */
    #[Url]
    public string $reason = '';

    /** The trip calculated (Trip::tripSpec()), empty before calculate(). */
    public array $trip = [];

    public string $name = '';

    public bool $public = false;

    /** The night to make a list for; empty for a list per night. */
    public string $listNight = '';

    public int $minutes = Trip::MINUTES_PER_OBJECT;

    public function mount(): void
    {
        $this->from  = $this->from ?: Carbon::today()->toDateString();
        $this->until = $this->until ?: Carbon::parse($this->from)->addDays(6)->toDateString();
        if (is_numeric($this->lat) && is_numeric($this->lon)) {
            $this->timezone = $this->timezone ?: Trip::guessTimezone((float) $this->lat, (float) $this->lon);
            $this->sqm      = $this->sqm ?: (string) Trip::suggestSqm((float) $this->lat, (float) $this->lon);
            // a shared link calculates at once; invalid values only show the form
            try {
                $this->calculate();
            } catch (\Illuminate\Validation\ValidationException) {
                $this->trip = [];
            }
        }
        $user             = Auth::user();
        $instruments      = RevisitObjects::instruments($user);
        $this->instrument = (string) (($instruments->firstWhere('id', (int) $this->instrument) ?? $instruments->firstWhere('id', (int) $user->stdtelescope) ?? $instruments->first())?->id ?? '');
        $this->home       = (string) (EquipmentGain::location($user, (int) $this->home ?: null)?->id ?? '');
    }

    /**
     * The place picked on the map: proposes its time zone and sky.
     */
    public function pick($latitude, $longitude): void
    {
        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return;
        }
        $latitude       = round(max(-90.0, min(90.0, (float) $latitude)), 4);
        $longitude      = round(fmod(fmod((float) $longitude + 180.0, 360.0) + 360.0, 360.0) - 180.0, 4);
        $this->lat      = (string) $latitude;
        $this->lon      = (string) $longitude;
        $this->timezone = Trip::guessTimezone($latitude, $longitude);
        $this->sqm      = (string) Trip::suggestSqm($latitude, $longitude);
        $this->trip     = [];
    }

    /**
     * Takes the trip of the form.
     */
    public function calculate(): void
    {
        $this->validate([
            'lat'      => ['required', 'numeric', 'between:-90,90'],
            'lon'      => ['required', 'numeric', 'between:-180,180'],
            'place'    => ['nullable', 'string', 'max:100'],
            'sqm'      => ['nullable', 'numeric', 'between:16,22.5'],
            'timezone' => ['required', 'timezone:all_with_bc'],
            'from'     => ['required', 'date_format:Y-m-d'],
            'until'    => ['required', 'date_format:Y-m-d', 'after_or_equal:from', 'before:'.Carbon::parse($this->from ?: 'today')->addDays(Trip::MAX_NIGHTS)->toDateString()],
        ], [
            'until.before' => __('A trip has at most :count nights.', ['count' => Trip::MAX_NIGHTS]),
            'lat.required' => __('Pick the place on the map.'),
            'lon.required' => __('Pick the place on the map.'),
        ], [
            'place'    => __('Name'),
            'sqm'      => __('SQM'),
            'timezone' => __('Timezone'),
            'from'     => __('First night'),
            'until'    => __('Last night'),
        ]);

        $this->trip = Trip::tripSpec([
            'latitude'  => (float) $this->lat,
            'longitude' => (float) $this->lon,
            'sqm'       => $this->sqm !== '' ? (float) $this->sqm : null,
            'timezone'  => $this->timezone,
            'from'      => $this->from,
            'until'     => $this->until,
            'name'      => $this->place,
        ]);
        $this->listNight = '';
    }

    public function retry(): void
    {
        $this->planner()?->retry();
    }

    public function create()
    {
        $planner = $this->planner();
        $this->validate([
            'name'      => ['nullable', 'string', 'max:250'],
            'minutes'   => ['required', 'integer', 'min:1', 'max:120'],
            'listNight' => ['nullable', 'in:'.implode(',', $planner?->nights() ?? [])],
        ], [], ['minutes' => __('Minutes per object')]);
        $user = Auth::user();
        abort_unless($user && $user->can('create', ObservingList::class), 403);

        $result = $planner && $planner->unavailable() === null ? $planner->result() : null;
        $lists  = $result ? $planner->createLists($result, $this->reasonFilter(), $this->listNight ?: null, $this->minutes, Str::limit(trim($this->name), 200, ''), $this->public) : [];
        if ($lists === []) {
            $this->addError('name', __('There are no objects to put on a list.'));

            return null;
        }
        if (count($lists) === 1) {
            session()->flash('success', __('Observing list created with :count object(s).', ['count' => $lists[0]->items()->count()]));

            return redirect()->route('observing-list.show', $lists[0]);
        }
        session()->flash('success', __(':count observing lists created, one for each night.', ['count' => count($lists)]));

        return redirect()->route('observing-lists.index');
    }

    private function planner(): ?Trip
    {
        return $this->trip === [] ? null : Trip::for(Auth::user(), $this->trip, (int) $this->instrument ?: null, (int) $this->home ?: null);
    }

    private function reasonFilter(): ?string
    {
        return in_array($this->reason, [Trip::LOW, Trip::FAINTER], true) ? $this->reason : null;
    }

    public function render()
    {
        $planner     = $this->planner();
        $unavailable = $planner?->unavailable();
        $result      = $planner && $unavailable === null ? $planner->result() : null;
        if ($planner && $unavailable === null && ! $result && ! $planner->pending()) {
            $planner->queue();
            // a sync queue (tests, no worker configured) has it now
            $result = $planner->result();
        }
        $decode = fn ($name) => html_entity_decode((string) $name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return view('livewire.trip-planner', [
            'planner'     => $planner,
            'unavailable' => $unavailable,
            'failed'      => $planner && $unavailable === null && ! $result && $planner->failed(),
            'result'      => $result,
            'shown'       => $result ? $planner->objects($result, $this->reasonFilter()) : null,
            'nights'      => $planner ? $planner->nightSummaries() : [],
            // the zone of the longitude (Etc/GMT-1) of a place without locations near it too
            'timezones'   => array_values(array_unique(array_filter([...\DateTimeZone::listIdentifiers(), $this->timezone]))),
            'instruments' => RevisitObjects::instruments(Auth::user())
                ->mapWithKeys(fn ($instrument) => [(int) $instrument->id => $decode($instrument->fullName())])
                ->all(),
            'locations' => EquipmentGain::locations(Auth::user())
                ->mapWithKeys(fn ($location) => [(int) $location->id => $decode($location->name)])
                ->all(),
        ]);
    }
}
