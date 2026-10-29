<?php

namespace App\Livewire;

use App\Models\Observation;
use App\Services\ObservingPartners as Partners;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Observing partners nearby (/observing-partners, App\Services\ObservingPartners):
 * the observer turns on being found, with a short note, and finds the
 * observers near their standard location who did the same, with the
 * starparties and clubs in the area.
 */
class ObservingPartners extends Component
{
    public bool $partnerSearch = false;

    public string $note = '';

    #[Url]
    public int $radius = Partners::DEFAULT_RADIUS;

    public function mount(): void
    {
        $user                = Auth::user();
        $this->partnerSearch = (bool) $user->partner_search;
        $this->note          = (string) $user->partner_note;
        $this->radius        = Partners::radius($this->radius);
    }

    public function save(): void
    {
        $this->validate([
            'note' => 'nullable|string|max:'.Partners::NOTE_LENGTH,
        ]);

        // One line of plain text
        $note = trim(preg_replace('/\s+/u', ' ', strip_tags($this->note)));
        Auth::user()->forceFill([
            'partner_search' => $this->partnerSearch,
            'partner_note'   => $note !== '' ? $note : null,
        ])->save();
        $this->note = $note;

        session()->flash('partners_saved', $this->partnerSearch
            ? __('Observers near you who look for partners can now find you.')
            : __('Observers near you can no longer find you.'));
    }

    public function render()
    {
        $user     = Auth::user();
        $radius   = Partners::radius($this->radius);
        $home     = Partners::home($user);
        $partners = Partners::nearby($user, $radius);
        // Only an observer who can be found finds others
        $shown = $user->partner_search ? $partners : collect();

        // The activity of the partners shown: their observations and the last one
        $activity = $shown->isEmpty() ? collect() : Observation::query()
            ->whereIn('observer_username', $shown->map(fn ($partner) => $partner['user']->username))
            ->groupBy('observer_username')
            ->selectRaw('observer_username, COUNT(*) as observations, MAX(observed_on) as last_observed')
            ->get()
            ->keyBy(fn ($row) => mb_strtolower($row->observer_username));

        return view('livewire.observing-partners', [
            'home'        => $home,
            'radius'      => $radius,
            'partners'    => $shown,
            'hiddenCount' => $user->partner_search ? 0 : $partners->count(),
            'activity'    => $activity,
            'starParties' => Partners::starParties($user, $radius),
            'clubs'       => Partners::clubs($shown),
        ]);
    }
}
