<?php

namespace App\Livewire;

use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Services\MessierMarathon;
use App\Support\EphemerisDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * The Messier marathon planner (/observing-lists/messier-marathon): the
 * nights of a season (March and April, or October and November) near the
 * new Moon at one of the observer's locations with the number of Messier
 * objects each lets see, and for the chosen night the order to see them in
 * (MessierMarathon), which can be made an observing list with the times.
 * With one of the observer's instruments (by default the standard
 * telescope), a faint object counts only while it stands high enough to be
 * seen with it.
 */
class MessierMarathonPlanner extends Component
{
    /** The season: the key of MessierMarathon::seasons() ('2027-spring'). */
    public string $season = '';

    /** One of the observer's locations; null for Location::forEphemerides(). */
    public ?int $locationId = null;

    /** One of the observer's instruments; null for the altitude alone. */
    public ?int $instrumentId = null;

    public int $minAltitude = MessierMarathon::MIN_ALTITUDE;

    public int $minutes = MessierMarathon::MINUTES_PER_OBJECT;

    /** The minutes to move to the next object and find it. */
    public int $gap = 0;

    /** The night planned (Y-m-d); empty for the best one. */
    public string $night = '';

    /** Whether every night of the season is listed, not only those near the new Moon. */
    public bool $allNights = false;

    public string $name = '';

    public bool $public = false;

    /** @var array<int, string>|null the instrument options of this request */
    private ?array $instrumentChoices = null;

    public function mount(): void
    {
        $location         = Location::forEphemerides(Auth::user());
        $this->locationId = $location && (int) $location->user_id === (int) Auth::id() ? (int) $location->id : null;
        $this->season     = $this->seasonOptions()[0]['key'];

        $standard           = (int) (Auth::user()?->stdtelescope ?? 0);
        $this->instrumentId = array_key_exists($standard, $this->instrumentOptions()) ? $standard : null;
    }

    /**
     * Other answers plan the best night again.
     */
    public function updated(string $property): void
    {
        if (in_array($property, ['season', 'locationId', 'instrumentId'], true)) {
            $this->night = '';
        }
    }

    public function plan(string $night): void
    {
        $this->night = $night;
    }

    public function create()
    {
        $this->validate();
        $user = Auth::user();
        abort_unless($user && $user->can('create', ObservingList::class), 403);

        $location = $this->location();
        $plan     = $location ? $this->marathon($location)->plan($this->chosenNight($location)) : null;
        if (! $plan || $plan['objects'] === []) {
            $this->addError('night', __('No Messier object is high enough in this night.'));

            return null;
        }

        $list = $this->marathon($location)->createList($user, $plan, $location, Str::limit(trim($this->name), 250, ''), $this->public);
        session()->flash('success', __('Observing list created with :count object(s).', ['count' => $list->items()->count()]));

        return redirect()->route('observing-list.show', $list);
    }

    protected function rules(): array
    {
        return [
            'season'       => ['required', 'in:'.implode(',', array_column($this->seasonOptions(), 'key'))],
            'locationId'   => ['nullable', 'integer', 'in:'.implode(',', array_keys($this->locationOptions()) ?: [0])],
            'instrumentId' => ['nullable', 'integer', 'in:'.implode(',', array_keys($this->instrumentOptions()) ?: [0])],
            'minAltitude'  => ['required', 'integer', 'min:0', 'max:30'],
            'minutes'      => ['required', 'integer', 'min:1', 'max:15'],
            'gap'          => ['required', 'integer', 'min:0', 'max:15'],
            'night'        => ['nullable', 'date_format:Y-m-d'],
            'name'         => ['nullable', 'string', 'max:250'],
        ];
    }

    /**
     * The season of today or the next one, and the two after it.
     *
     * @return list<array{key: string, year: int, season: string}>
     */
    private function seasonOptions(): array
    {
        return MessierMarathon::seasons(EphemerisDate::today(EphemerisDate::timezone($this->location())));
    }

    /**
     * The chosen season, else the first one offered.
     *
     * @return array{key: string, year: int, season: string}
     */
    private function chosenSeason(): array
    {
        $options = $this->seasonOptions();

        return collect($options)->firstWhere('key', $this->season) ?? $options[0];
    }

    /**
     * The chosen location, one of the observer's, else the location of the
     * ephemerides.
     */
    private function location(): ?Location
    {
        $location = $this->locationId
            ? Location::where('user_id', Auth::id())->find($this->locationId)
            : null;
        $location ??= Location::forEphemerides(Auth::user());

        return $location && $location->hasValidCoordinates() ? $location : null;
    }

    /**
     * The name of the location when it is the observer's own; another
     * observer's location is only named by its latitude.
     */
    private function placeName(Location $location): ?string
    {
        return (int) $location->user_id === (int) Auth::id() ? html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
    }

    private function marathon(Location $location): MessierMarathon
    {
        return MessierMarathon::forLocation(
            $location,
            EphemerisDate::timezone($location),
            (float) max(0, min(30, $this->minAltitude)),
            max(1, min(15, $this->minutes)),
            max(0, min(15, $this->gap)),
            $this->instrument(),
            Auth::user(),
        );
    }

    /**
     * The chosen instrument, one of the observer's active ones.
     */
    private function instrument(): ?Instrument
    {
        return $this->instrumentId && array_key_exists($this->instrumentId, $this->instrumentOptions())
            ? Instrument::where('user_id', Auth::id())->find($this->instrumentId)
            : null;
    }

    /**
     * The observer's active instruments with an aperture, not the naked eye.
     *
     * @return array<int, string>
     */
    private function instrumentOptions(): array
    {
        if (! Auth::id()) {
            return [];
        }

        return $this->instrumentChoices ??= Instrument::where('user_id', Auth::id())
            ->where('active', true)
            ->where('aperture_mm', '>', 0)
            ->orderBy('name')
            ->get()
            ->reject(fn (Instrument $instrument) => $instrument->instrument_type?->name === 'Naked Eye')
            ->mapWithKeys(fn (Instrument $instrument) => [(int) $instrument->id => html_entity_decode($instrument->fullName(), ENT_QUOTES | ENT_HTML5, 'UTF-8')])
            ->all();
    }

    /**
     * The night asked for when it is in the season, else the best one.
     */
    private function chosenNight(Location $location, ?array $nights = null): string
    {
        $season = $this->chosenSeason();
        $nights ??= $this->marathon($location)->nights($season['year'], $season['season'], EphemerisDate::today(EphemerisDate::timezone($location)));
        $dates = array_column($nights, 'night');
        if ($this->night !== '' && in_array($this->night, $dates, true)) {
            return $this->night;
        }

        foreach ($nights as $night) {
            if ($night['best']) {
                return $night['night'];
            }
        }

        return $dates[0];
    }

    /**
     * The observer's active locations with coordinates.
     *
     * @return array<int, string>
     */
    private function locationOptions(): array
    {
        return Location::where('user_id', Auth::id())
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'latitude', 'longitude'])
            ->filter(fn (Location $location) => $location->hasValidCoordinates())
            ->mapWithKeys(fn (Location $location) => [(int) $location->id => html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')])
            ->all();
    }

    /**
     * The objects of the plan the observer has seen (observations that were
     * not "not seen"), by objects.name in lower case.
     *
     * @return array<string, true>
     */
    private function seen(array $names): array
    {
        $user = Auth::user();
        if (! $user || $names === []) {
            return [];
        }

        return DB::table('observations')
            ->where('observer_username', $user->username)
            ->where('target_table', 'objects')
            ->whereIn('target_key', $names)
            ->whereNotExists(fn ($details) => $details->selectRaw('1')
                ->from('deepsky_observation_details')
                ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
                ->where('deepsky_observation_details.visibility', Observation::VISIBILITY_NOT_SEEN))
            ->distinct()
            ->pluck('target_key')
            ->mapWithKeys(fn ($name) => [mb_strtolower((string) $name) => true])
            ->all();
    }

    public function render()
    {
        $season       = $this->chosenSeason();
        $this->season = $season['key'];

        $location = $this->location();
        $marathon = $location ? $this->marathon($location) : null;
        $nights   = $marathon ? $marathon->nights($season['year'], $season['season'], EphemerisDate::today(EphemerisDate::timezone($location))) : [];
        $night    = $marathon ? $this->chosenNight($location, $nights) : null;
        $plan     = $marathon ? $marathon->plan($night) : null;
        $names    = $plan ? array_merge(array_column($plan['objects'], 'list_name'), array_column($plan['missed'], 'list_name')) : [];

        return view('livewire.messier-marathon-planner', [
            'timezone'          => $location ? EphemerisDate::timezone($location) : 'UTC',
            'place'             => $location ? $this->placeName($location) : null,
            'latitude'          => $location ? (int) round((float) $location->latitude) : null,
            'locationOptions'   => $this->locationOptions(),
            'instrumentOptions' => $this->instrumentOptions(),
            'instrumentName'    => $marathon?->instrumentName(),
            'seasonOptions'     => $this->seasonOptions(),
            'chosenSeason'      => $season,
            'total'             => $marathon?->total() ?? 0,
            'nights'            => $this->allNights ? $nights : array_values(array_filter($nights, fn (array $n) => $n['near_new_moon'])),
            'plan'              => $plan,
            'seen'              => $this->seen($names),
            'defaultName'       => $marathon && $night ? $marathon->listName($night) : '',
        ]);
    }
}
