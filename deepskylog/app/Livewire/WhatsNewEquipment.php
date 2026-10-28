<?php

namespace App\Livewire;

use App\Models\ObservingList;
use App\Services\EquipmentGain;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * "What's new for me" (/instrument/{user}/{instrument}/whats-new,
 * /eyepiece/{user}/{eyepiece}/whats-new): the objects an observer's
 * telescope or eyepiece lets them see at one of their locations that they
 * could not see before and have not seen yet (EquipmentGain), with a button
 * that makes them an observing list. "What if I bought…"
 * (/instrument/what-if, kind PLANNED) asks for a telescope or binoculars
 * the observer does not have instead; the values are kept in the query
 * string and only calculated with calculate(), not while typing. The result
 * is calculated on the queue; the page polls until it is there.
 */
class WhatsNewEquipment extends Component
{
    public string $kind = EquipmentGain::INSTRUMENT;

    public int $equipmentId = 0;

    public ?int $locationId = null;

    /** For a telescope: the ids of the instruments it is compared with. */
    public array $compare = [];

    public string $name = '';

    /** For a planned instrument: telescope or binoculars. */
    #[Url(as: 'type')]
    public string $plannedType = 'telescope';

    /** In inches for a telescope when the observer uses them, else mm. */
    #[Url]
    public string $aperture = '';

    /** In inches when the observer uses them, else mm. */
    #[Url(as: 'focal_length')]
    public string $focalLength = '';

    /** Of binoculars. */
    #[Url]
    public string $magnification = '';

    /** The planned instrument calculated (EquipmentGain::plannedSpec()), empty before calculate(). */
    public array $planned = [];

    public bool $public = false;

    public function mount(string $kind, int $equipmentId = 0): void
    {
        $this->kind        = $kind;
        $this->equipmentId = $equipmentId;
        if ($kind === EquipmentGain::PLANNED && $this->aperture !== '') {
            // a shared link calculates at once; invalid values only show the form
            try {
                $this->calculate();
            } catch (\Illuminate\Validation\ValidationException) {
                $this->planned = [];
            }
        }
        $gain = $this->gain();
        abort_unless($gain, 404);
        $this->locationId = $gain->location?->id;
        $this->compareAll();
    }

    public function compareAll(): void
    {
        $this->compare = $this->comparesInstruments()
            ? EquipmentGain::for(Auth::user(), $this->kind, $this->equipmentId, null, null, $this->planned)->comparable()->pluck('id')->map(fn ($id) => (string) $id)->all()
            : [];
    }

    /**
     * Calculates again after the calculation failed.
     */
    public function retry(): void
    {
        $this->gain()?->retry();
    }

    /**
     * Takes the planned instrument of the form.
     */
    public function calculate(): void
    {
        $binoculars = $this->plannedType === 'binoculars';
        // binoculars are named in mm (10×50)
        $inches = ! $binoculars && Auth::user()->showInches;
        $this->validate([
            'plannedType'   => ['required', 'in:telescope,binoculars'],
            'aperture'      => ['required', 'numeric', 'min:'.($inches ? 0.5 : 10), 'max:'.($inches ? 80 : 2000)],
            'focalLength'   => ['nullable', 'numeric', 'min:'.($inches ? 2 : 50), 'max:'.($inches ? 800 : 20000)],
            'magnification' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [], [
            'aperture'      => __('aperture'),
            'focalLength'   => __('focal length'),
            'magnification' => __('magnification'),
        ]);
        $factor        = $inches ? 25.4 : 1.0;
        $this->planned = EquipmentGain::plannedSpec([
            'binoculars'    => $binoculars,
            'aperture'      => (float) $this->aperture                                  * $factor,
            'focal_length'  => $this->focalLength   !== '' ? (float) $this->focalLength * $factor : null,
            'magnification' => $this->magnification !== '' ? (int) $this->magnification : null,
        ]);
    }

    public function compareNone(): void
    {
        $this->compare = [];
    }

    public function create()
    {
        $this->validate(['name' => ['nullable', 'string', 'max:250']]);
        $user = Auth::user();
        abort_unless($user && $user->can('create', ObservingList::class), 403);

        $gain   = $this->gain();
        $result = $gain?->result();
        $list   = $result ? $gain->createList($result, Str::limit(trim($this->name), 250, ''), $this->public) : null;
        if (! $list) {
            $this->addError('name', __('There are no objects to put on a list.'));

            return null;
        }
        session()->flash('success', __('Observing list created with :count object(s).', ['count' => $list->items()->count()]));

        return redirect()->route('observing-list.show', $list);
    }

    private function gain(): ?EquipmentGain
    {
        return EquipmentGain::for(Auth::user(), $this->kind, $this->equipmentId, $this->locationId, $this->comparesInstruments() ? $this->compare : null, $this->planned);
    }

    private function comparesInstruments(): bool
    {
        return $this->kind !== EquipmentGain::EYEPIECE;
    }

    public function render()
    {
        $gain = $this->gain();
        // a planned instrument waits for the form
        $entered     = $this->kind !== EquipmentGain::PLANNED || $this->planned !== [];
        $unavailable = $entered ? $gain?->unavailable() : null;
        $result      = $entered && $unavailable === null ? $gain?->result() : null;
        if ($gain && $entered && $unavailable === null && ! $result && ! $gain->pending()) {
            $gain->queue();
            // a sync queue (tests, no worker configured) has it now
            $result = $gain->result();
        }
        $failed = $gain && $entered && $unavailable === null && ! $result && $gain->failed();

        return view('livewire.whats-new-equipment', [
            'gain'        => $gain,
            'entered'     => $entered,
            'inches'      => (bool) Auth::user()->showInches,
            'failed'      => $failed,
            'unavailable' => $unavailable,
            'result'      => $result,
            'shown'       => $result ? $gain->objects($result) : null,
            'comparable'  => $gain && $this->comparesInstruments()
                ? $gain->comparable()->get()->mapWithKeys(fn ($instrument) => [(int) $instrument->id => html_entity_decode((string) $instrument->fullName(), ENT_QUOTES | ENT_HTML5, 'UTF-8')])->all()
                : [],
            'locations' => EquipmentGain::locations(Auth::user())
                ->mapWithKeys(fn ($location) => [(int) $location->id => html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')])
                ->all(),
        ]);
    }
}
