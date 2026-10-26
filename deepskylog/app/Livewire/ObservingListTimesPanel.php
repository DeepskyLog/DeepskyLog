<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\ObservingList;
use App\Services\NightWindow;
use App\Services\ObservingListTimes;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The times of an observing list for a night, on the list page: the observer
 * chooses the night, one of their locations, the hours (empty for the dark
 * hours), the lowest altitude, the minutes per object and between objects,
 * sees the times
 * ObservingListTimes proposes, and saves them in the list (its owner) or as a
 * new list (any observer who makes lists). The owner can also remove them.
 */
class ObservingListTimesPanel extends Component
{
    #[Locked]
    public int $listId;

    #[Locked]
    public bool $isOwner = false;

    public string $night = '';

    public ?int $locationId = null;

    public string $from = '';

    public string $until = '';

    public int $minAltitude = ObservingListTimes::MIN_ALTITUDE;

    public int $minutes = ObservingListTimes::MINUTES_PER_OBJECT;

    /** The minutes to move to the next object and find it. */
    public int $gap = ObservingListTimes::MINUTES_BETWEEN;

    public string $newName = '';

    /** Whether the proposed times are shown. */
    public bool $showProposal = false;

    public function mount(int $listId): void
    {
        $list = ObservingList::findOrFail($listId);
        abort_unless(Gate::allows('view', $list), 403);

        $this->listId  = $list->id;
        $this->isOwner = Auth::id() === $list->owner_user_id;

        $options          = $this->locationOptions();
        $planned          = $list->planned_location_id && isset($options[(int) $list->planned_location_id]) ? (int) $list->planned_location_id : null;
        $standard         = Auth::user()?->stdlocation && isset($options[(int) Auth::user()->stdlocation]) ? (int) Auth::user()->stdlocation : null;
        $this->locationId = $planned ?? $standard ?? (array_key_first($options) ?: null);

        $timezone    = EphemerisDate::timezone($this->location());
        $this->night = $this->isOwner && $list->planned_night
            ? $list->planned_night->toDateString()
            : EphemerisDate::current($timezone);
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

    public function updatedFrom(mixed $value): void
    {
        $this->from = NightListWizard::time($value);
    }

    public function updatedUntil(mixed $value): void
    {
        $this->until = NightListWizard::time($value);
    }

    /**
     * Changing an answer hides the times of the previous ones.
     */
    public function updated(string $property): void
    {
        if (! in_array($property, ['newName', 'showProposal'], true)) {
            $this->showProposal = false;
        }
    }

    public function propose(): void
    {
        $this->validate();
        $this->showProposal = true;
    }

    public function save()
    {
        abort_unless($this->isOwner, 403);
        $list     = $this->list();
        $proposal = $this->proposal();
        if ($proposal === null) {
            return null;
        }

        ObservingListTimes::save($list, $this->location(), $proposal);
        session()->flash('success', __('Times saved for :count object(s).', ['count' => count($proposal['objects'])]));

        return redirect()->route('observing-list.show', $list);
    }

    public function saveAsNew()
    {
        $user = Auth::user();
        abort_unless($user && $user->can('create', ObservingList::class), 403);
        $proposal = $this->proposal();
        if ($proposal === null) {
            return null;
        }

        $list = ObservingListTimes::saveAsNew($user, $this->list(), $this->location(), $proposal, Str::limit(trim($this->newName), 250, ''));
        session()->flash('success', __('Observing list created with :count object(s).', ['count' => $list->items()->count()]));

        return redirect()->route('observing-list.show', $list);
    }

    public function clear()
    {
        abort_unless($this->isOwner, 403);
        $list = $this->list();
        ObservingListTimes::clear($list);
        session()->flash('success', __('The times of the list are removed.'));

        return redirect()->route('observing-list.show', $list);
    }

    protected function rules(): array
    {
        return [
            'night'       => ['required', 'date_format:Y-m-d'],
            'locationId'  => ['required', 'integer', 'in:'.(implode(',', array_keys($this->locationOptions())) ?: '0')],
            'from'        => ['nullable', 'required_with:until', 'date_format:H:i'],
            'until'       => ['nullable', 'required_with:from', 'date_format:H:i'],
            'minAltitude' => ['required', 'integer', 'min:0', 'max:60'],
            'minutes'     => ['required', 'integer', 'min:1', 'max:60'],
            'gap'         => ['required', 'integer', 'min:0', 'max:30'],
            'newName'     => ['nullable', 'string', 'max:250'],
        ];
    }

    /**
     * The proposed times for the answers, or null with an error.
     */
    private function proposal(): ?array
    {
        $this->validate();
        $proposal = ObservingListTimes::propose($this->list(), $this->location(), $this->night, $this->from ?: null, $this->until ?: null, (float) $this->minAltitude, $this->minutes, $this->gap);
        if ($proposal === null) {
            $this->addError('from', __('It does not get dark during this night at this location: choose the hours.'));
        }

        return $proposal;
    }

    private function list(): ObservingList
    {
        return ObservingList::findOrFail($this->listId);
    }

    private function location(): ?Location
    {
        return $this->locationId ? Location::where('user_id', Auth::id())->find($this->locationId) : null;
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

    public function render()
    {
        $list     = $this->list();
        $location = $this->location();
        $timezone = EphemerisDate::timezone($location);

        $dark = null;
        if ($location && preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->night)) {
            $window = NightWindow::for((float) $location->latitude, (float) $location->longitude, $this->night, $timezone);
            $dark   = $window ? [
                'from'     => Carbon::createFromTimestamp($window->start, $timezone)->format('H:i'),
                'until'    => Carbon::createFromTimestamp($window->end, $timezone)->format('H:i'),
                'twilight' => $window->twilight,
            ] : false;
        }

        $proposal = null;
        if ($this->showProposal && $location && ! $this->getErrorBag()->any()) {
            $proposal = ObservingListTimes::propose($list, $location, $this->night, $this->from ?: null, $this->until ?: null, (float) $this->minAltitude, $this->minutes, $this->gap);
        }

        return view('livewire.observing-list-times-panel', [
            'list'            => $list,
            'locationOptions' => $this->locationOptions(),
            'timezone'        => $timezone,
            'dark'            => $dark,
            'proposal'        => $proposal,
            'plannedNight'    => $list->planned_night && $list->hasPlannedTimes() ? $list->planned_night : null,
            'defaultName'     => preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->night) ? ObservingListTimes::newListName($list, $this->night) : '',
            'canCreate'       => (bool) Auth::user()?->can('create', ObservingList::class),
        ]);
    }
}
