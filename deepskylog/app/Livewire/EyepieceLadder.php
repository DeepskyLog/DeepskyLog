<?php

namespace App\Livewire;

use App\Models\Instrument;
use App\Models\InstrumentSet;
use App\Services\EquipmentGain;
use App\Services\EyepieceGaps;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * The eyepiece gap analysis on the page of the observer's own telescope: the
 * ladder of their magnifications with it, the gaps and the eyepieces that
 * fill them (EyepieceGaps), each with the number of objects it would let them
 * see at their standard location that they have not seen
 * (EquipmentGain::PLANNED_EYEPIECE), calculated on the queue; the component
 * polls until the counts are there. With an instrument set chosen
 * (`?set=`), only the eyepieces and lenses of that set count.
 */
class EyepieceLadder extends Component
{
    public int $instrumentId;

    /** The id of the instrument set whose eyepieces and lenses are used; empty for all. */
    #[Url(as: 'set')]
    public string $instrumentSet = '';

    public function mount(int $instrumentId): void
    {
        $this->instrumentId = $instrumentId;
    }

    public function render()
    {
        $user       = Auth::user();
        $instrument = Instrument::where('user_id', $user->id)->findOrFail($this->instrumentId);
        $set        = EyepieceGaps::set($user, $this->instrumentSet);
        $ladder     = new EyepieceGaps($user, $instrument, $set);
        $gaps       = $ladder->gaps();
        $location   = EquipmentGain::location($user, null);

        $waiting = false;
        foreach ($gaps as $i => $gap) {
            foreach ($gap['suggestions'] as $j => $suggestion) {
                $gain   = EquipmentGain::forPlannedEyepiece($user, $instrument, [...$suggestion, 'instrument_set' => $set?->id], $location);
                $result = null;
                if ($gain->unavailable() === null) {
                    $result = $gain->result();
                    if (! $result && ! $gain->pending() && $gain->queue()) {
                        // a sync queue (tests, no worker configured) has it now
                        $result = $gain->result();
                    }
                    $waiting = $waiting || (! $result && ! $gain->failed());
                }
                $gaps[$i]['suggestions'][$j]['objects'] = $result ? $gain->unseenTotal($result) : null;
                $gaps[$i]['suggestions'][$j]['url']     = route('instrument.what-if-eyepiece', [
                    $user->slug,
                    $instrument->slug,
                    'focal_length'   => $suggestion['focal_length'],
                    'apparent_field' => $suggestion['apparent_fov'],
                    'set'            => $set?->id,
                ]);
            }
        }

        return view('livewire.eyepiece-ladder', [
            'ladder'   => $ladder,
            'steps'    => $ladder->steps(),
            'gaps'     => $gaps,
            'location' => $location,
            'waiting'  => $waiting,
            'sets'     => InstrumentSet::where('user_id', $user->id)->orderBy('name')->get(['id', 'name'])
                ->mapWithKeys(fn (InstrumentSet $set) => [(int) $set->id => html_entity_decode((string) $set->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')])
                ->all(),
        ]);
    }
}
