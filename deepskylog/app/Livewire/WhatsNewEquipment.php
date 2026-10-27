<?php

namespace App\Livewire;

use App\Models\ObservingList;
use App\Services\EquipmentGain;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * "What's new for me" (/instrument/{user}/{instrument}/whats-new,
 * /eyepiece/{user}/{eyepiece}/whats-new): the objects an observer's
 * telescope or eyepiece lets them see at one of their locations that they
 * could not see before and have not seen yet (EquipmentGain), with a button
 * that makes them an observing list. The result is calculated on the queue;
 * the page polls until it is there.
 */
class WhatsNewEquipment extends Component
{
    public string $kind = EquipmentGain::INSTRUMENT;

    public int $equipmentId = 0;

    public ?int $locationId = null;

    /** For a telescope: the ids of the instruments it is compared with. */
    public array $compare = [];

    public string $name = '';

    public bool $public = false;

    public function mount(string $kind, int $equipmentId): void
    {
        $this->kind        = $kind;
        $this->equipmentId = $equipmentId;
        $gain              = $this->gain();
        abort_unless($gain, 404);
        $this->locationId = $gain->location?->id;
        $this->compareAll();
    }

    public function compareAll(): void
    {
        $this->compare = $this->kind === EquipmentGain::INSTRUMENT
            ? EquipmentGain::for(Auth::user(), $this->kind, $this->equipmentId, null)->comparable()->pluck('id')->map(fn ($id) => (string) $id)->all()
            : [];
    }

    /**
     * Calculates again after the calculation failed.
     */
    public function retry(): void
    {
        $this->gain()?->retry();
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
        return EquipmentGain::for(Auth::user(), $this->kind, $this->equipmentId, $this->locationId, $this->kind === EquipmentGain::INSTRUMENT ? $this->compare : null);
    }

    public function render()
    {
        $gain        = $this->gain();
        $unavailable = $gain?->unavailable();
        $result      = $unavailable === null ? $gain?->result() : null;
        if ($gain && $unavailable === null && ! $result && ! $gain->pending()) {
            $gain->queue();
            // a sync queue (tests, no worker configured) has it now
            $result = $gain->result();
        }
        $failed = $gain && $unavailable === null && ! $result && $gain->failed();

        return view('livewire.whats-new-equipment', [
            'gain'        => $gain,
            'failed'      => $failed,
            'unavailable' => $unavailable,
            'result'      => $result,
            'shown'       => $result ? $gain->objects($result) : null,
            'comparable'  => $gain && $gain->kind === EquipmentGain::INSTRUMENT
                ? $gain->comparable()->get()->mapWithKeys(fn ($instrument) => [(int) $instrument->id => html_entity_decode((string) $instrument->fullName(), ENT_QUOTES | ENT_HTML5, 'UTF-8')])->all()
                : [],
            'locations' => EquipmentGain::locations(Auth::user())
                ->mapWithKeys(fn ($location) => [(int) $location->id => html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')])
                ->all(),
        ]);
    }
}
