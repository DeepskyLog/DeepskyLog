<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\ObservingList;
use App\Services\NightWindow;
use App\Services\TonightsBest;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Make an observing list for a night (/observing-lists/night): the observer
 * answers which night and which part of it, which objects and which catalogs
 * or lists come first, whether seen objects may be on it, how difficult and
 * how many; TonightsBest chooses the objects, shown before the list is made.
 */
class NightListWizard extends Component
{
    /**
     * The kinds of objects offered: groups of deepskytypes codes, and the
     * planets and comets (SOLAR_SYSTEM_GROUPS).
     */
    public const TYPE_GROUPS = [
        'galaxies'           => ['GALXY', 'GALCL', 'GXADN', 'GXAGC', 'GACAN'],
        'open_clusters'      => ['OPNCL'],
        'globular_clusters'  => ['GLOCL'],
        'planetary_nebulae'  => ['PLNNB'],
        'nebulae'            => ['BRTNB', 'EMINB', 'HII', 'REFNB', 'WRNEB'],
        'dark_nebulae'       => ['DRKNB'],
        'supernova_remnants' => ['SNREM'],
        'asterisms'          => ['ASTER'],
        'double_stars'       => ['DS'],
        'planets'            => [],
        'comets'             => [],
    ];

    private const SOLAR_SYSTEM_GROUPS = ['planets', 'comets'];

    public string $night = '';

    public string $period = 'night';

    public string $from = '';

    public string $until = '';

    /** @var list<string> keys of TYPE_GROUPS; none is all kinds but double stars */
    public array $typeGroups = [];

    /** @var list<string> 'catalog:<name>' or 'list:<id>' */
    public array $sources = [];

    /** 'first': the objects of the sources first; 'only': only their objects. */
    public string $sourceMode = 'first';

    public string $seen = 'few';

    public string $difficulty = 'mixed';

    public int $count = 30;

    public string $name = '';

    public bool $public = false;

    /** Whether the objects for the answers are shown. */
    public bool $showPreview = false;

    public function mount(): void
    {
        $this->night = EphemerisDate::chosen($this->timezone()) ?? EphemerisDate::today($this->timezone());

        // ?night=Y-m-d, from the forecast of the coming nights and its message
        $night = request()->query('night');
        if (is_string($night) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $night) && checkdate((int) substr($night, 5, 2), (int) substr($night, 8, 2), (int) substr($night, 0, 4))) {
            $this->night = $night;
        }
    }

    /**
     * The date picker may send a moment ("2026-11-09T00:00:00+01:00"): the
     * night is its date.
     */
    public function updatedNight(mixed $value): void
    {
        if (is_string($value) && preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $match)) {
            $this->night = $match[1];
        }
    }

    /**
     * The time pickers give "10:30 PM" on the 12-hour clock: the times are
     * kept as 24-hour "22:30".
     */
    public function updatedFrom(mixed $value): void
    {
        $this->from = self::time($value);
    }

    public function updatedUntil(mixed $value): void
    {
        $this->until = self::time($value);
    }

    public static function time(mixed $value): string
    {
        $value = trim((string) $value);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?\s*([AaPp][Mm])?$/', $value, $match)) {
            $hours = (int) $match[1] % (isset($match[3]) ? 12 : 24);
            if (isset($match[3]) && strtoupper($match[3]) === 'PM') {
                $hours += 12;
            }
            if ($hours < 24 && (int) $match[2] < 60) {
                return sprintf('%02d:%s', $hours, $match[2]);
            }
        }

        return $value;
    }

    /**
     * Changing an answer hides the objects of the previous ones.
     */
    public function updated(string $property): void
    {
        if (! in_array($property, ['name', 'public', 'showPreview'], true)) {
            $this->showPreview = false;
        }
    }

    public function preview(): void
    {
        $this->validate();
        $this->showPreview = true;
    }

    public function create()
    {
        $this->validate();
        $user = Auth::user();
        abort_unless($user && $user->can('create', ObservingList::class), 403);

        $result = TonightsBest::for($user, $this->options());
        $list   = $result ? TonightsBest::createList($user, $result, Str::limit(trim($this->name), 250, ''), $this->public) : null;
        if (! $list) {
            $this->addError('count', __('No objects match these answers. Choose another part of the night, other objects or fewer sources.'));

            return null;
        }

        session()->flash('success', __('Observing list created with :count object(s).', ['count' => $list->items()->count()]));

        return redirect()->route('observing-list.show', $list);
    }

    protected function rules(): array
    {
        return [
            'night'        => ['required', 'date_format:Y-m-d'],
            'period'       => ['required', 'in:'.implode(',', TonightsBest::PERIODS)],
            'from'         => ['nullable', 'required_if:period,custom', 'date_format:H:i'],
            'until'        => ['nullable', 'required_if:period,custom', 'date_format:H:i'],
            'typeGroups'   => ['array'],
            'typeGroups.*' => ['in:'.implode(',', array_keys(self::TYPE_GROUPS))],
            'sources'      => ['array', 'max:20'],
            'sourceMode'   => ['required', 'in:first,only'],
            'seen'         => ['required', 'in:'.implode(',', TonightsBest::SEEN_OPTIONS)],
            'difficulty'   => ['required', 'in:'.implode(',', TonightsBest::DIFFICULTIES)],
            'count'        => ['required', 'integer', 'min:'.TonightsBest::MIN_COUNT, 'max:'.TonightsBest::MAX_COUNT],
            'name'         => ['nullable', 'string', 'max:250'],
        ];
    }

    /**
     * The answers as options of TonightsBest. Without a kind chosen, all kinds
     * but double stars (TonightsBest::EXCLUDED_TYPES).
     */
    private function options(): array
    {
        $groups  = $this->typeGroups ?: array_keys(self::TYPE_GROUPS);
        $deepsky = array_values(array_diff($groups, self::SOLAR_SYSTEM_GROUPS));

        return [
            'night'        => $this->night,
            'period'       => $this->period,
            'from'         => $this->from ?: null,
            'until'        => $this->until ?: null,
            'types'        => $this->typeGroups === [] ? [] : array_merge(...array_map(fn (string $group) => self::TYPE_GROUPS[$group] ?? [], $deepsky ?: [])),
            'deepsky'      => $this->typeGroups === [] || $deepsky !== [],
            'planets'      => in_array('planets', $groups, true),
            'comets'       => in_array('comets', $groups, true),
            'sources'      => $this->sources,
            'sources_only' => $this->sourceMode === 'only' && $this->sources !== [],
            'seen'         => $this->seen,
            'difficulty'   => $this->difficulty,
            'count'        => $this->count,
        ];
    }

    private function timezone(): string
    {
        return EphemerisDate::timezone(Location::forEphemerides(Auth::user()));
    }

    /**
     * The catalogs and the observer's own and subscribed lists, keyed as the
     * sources of TonightsBest.
     *
     * @return array<string, string>
     */
    private function sourceOptions(): array
    {
        $catalogs = Cache::remember('night_list:catalogs', 3600, fn () => DB::table('objectnames')
            ->whereNotNull('catalog')
            ->where('catalog', '!=', '')
            ->distinct()
            ->orderBy('catalog')
            ->pluck('catalog')
            ->all());
        $options = [];
        foreach ($catalogs as $catalog) {
            $options['catalog:'.$catalog] = __('Catalog: :name', ['name' => $catalog]);
        }

        $user  = Auth::user();
        $lists = $user->observingLists()->get(['observing_lists.id', 'observing_lists.name'])
            ->concat($user->subscribedObservingLists()->get(['observing_lists.id', 'observing_lists.name']))
            ->unique('id')
            ->sortBy(fn (ObservingList $list) => Str::lower((string) $list->name));
        foreach ($lists as $list) {
            $options['list:'.$list->id] = __('Observing list: :name', ['name' => html_entity_decode((string) $list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')]);
        }

        return $options;
    }

    /**
     * The location and telescope the objects are chosen for. Without a
     * standard location the ephemerides use another observer's location,
     * of which only the latitude is shown.
     *
     * @return array{location: ?string, latitude: ?int, instrument: ?string}
     */
    private function equipment(?Location $location): array
    {
        $user       = Auth::user();
        $standard   = $location && (int) $user->stdlocation === (int) $location->id;
        $instrument = $location ? TonightsBest::instrument($user, $location) : null;

        return [
            'location'   => $standard ? html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : null,
            'latitude'   => $location ? (int) round((float) $location->latitude) : null,
            'instrument' => $instrument ? html_entity_decode($instrument->fullName(), ENT_QUOTES | ENT_HTML5, 'UTF-8') : null,
        ];
    }

    public function render()
    {
        $location = Location::forEphemerides(Auth::user());
        $dark     = null;
        if ($location && $location->hasValidCoordinates() && preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->night)) {
            $timezone = EphemerisDate::timezone($location);
            $window   = NightWindow::for((float) $location->latitude, (float) $location->longitude, $this->night, $timezone);
            $dark     = $window ? [
                'from'     => Carbon::createFromTimestamp($window->start, $timezone)->format('H:i'),
                'until'    => Carbon::createFromTimestamp($window->end, $timezone)->format('H:i'),
                'twilight' => $window->twilight,
            ] : false;
        }

        $result = $this->showPreview && ! $this->getErrorBag()->any() ? TonightsBest::for(Auth::user(), $this->options()) : null;

        return view('livewire.night-list-wizard', [
            'dark'          => $dark,
            'equipment'     => $this->equipment($location),
            'result'        => $result,
            'sourceOptions' => $this->sourceOptions(),
            'defaultName'   => TonightsBest::listName(['night' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->night) ? $this->night : now()->toDateString()]),
        ]);
    }
}
