<?php

namespace App\Livewire;

use App\Models\ObservingList;
use App\Services\EquipmentGain;
use App\Services\RevisitObjects as Revisit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * "Revisit with your telescope" (/observing-lists/revisit): the objects the
 * observer has seen only with a much smaller instrument, or never drew, that
 * their telescope shows much better (App\Services\RevisitObjects), with a
 * button that makes them an observing list. The result is calculated on the
 * queue; the page polls until it is there.
 */
class RevisitObjects extends Component
{
    /** One of the observer's instruments; empty for the standard telescope. */
    #[Url]
    public string $instrument = '';

    #[Url]
    public string $location = '';

    /** Revisit::SMALLER, Revisit::UNDRAWN or empty for both. */
    #[Url]
    public string $reason = '';

    public string $name = '';

    public bool $public = false;

    public function mount(): void
    {
        $revisit          = $this->revisit();
        $this->instrument = (string) ($revisit->instrument?->id ?? '');
        $this->location   = (string) ($revisit->location?->id ?? '');
    }

    public function retry(): void
    {
        $this->revisit()->retry();
    }

    public function create()
    {
        $this->validate(['name' => ['nullable', 'string', 'max:250']]);
        $user = Auth::user();
        abort_unless($user && $user->can('create', ObservingList::class), 403);

        $revisit = $this->revisit();
        $result  = $revisit->unavailable() === null ? $revisit->result() : null;
        $list    = $result ? $revisit->createList($result, $this->reasonFilter(), Str::limit(trim($this->name), 250, ''), $this->public) : null;
        if (! $list) {
            $this->addError('name', __('There are no objects to put on a list.'));

            return null;
        }
        session()->flash('success', __('Observing list created with :count object(s).', ['count' => $list->items()->count()]));

        return redirect()->route('observing-list.show', $list);
    }

    private function revisit(): Revisit
    {
        return Revisit::for(Auth::user(), (int) $this->instrument ?: null, (int) $this->location ?: null);
    }

    private function reasonFilter(): ?string
    {
        return in_array($this->reason, [Revisit::SMALLER, Revisit::UNDRAWN], true) ? $this->reason : null;
    }

    public function render()
    {
        $revisit     = $this->revisit();
        $unavailable = $revisit->unavailable();
        $result      = $unavailable === null ? $revisit->result() : null;
        if ($unavailable === null && ! $result && ! $revisit->pending()) {
            $revisit->queue();
            // a sync queue (tests, no worker configured) has it now
            $result = $revisit->result();
        }
        $decode = fn ($name) => html_entity_decode((string) $name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return view('livewire.revisit-objects', [
            'revisit'     => $revisit,
            'unavailable' => $unavailable,
            'failed'      => $unavailable === null && ! $result && $revisit->failed(),
            'result'      => $result,
            'shown'       => $result ? $revisit->objects($result, $this->reasonFilter()) : null,
            'inches'      => (bool) Auth::user()->showInches,
            'instruments' => Revisit::instruments(Auth::user())
                ->mapWithKeys(fn ($instrument) => [(int) $instrument->id => $decode($instrument->fullName())])
                ->all(),
            'locations' => EquipmentGain::locations(Auth::user())
                ->mapWithKeys(fn ($location) => [(int) $location->id => $decode($location->name)])
                ->all(),
        ]);
    }
}
