<?php

namespace App\Http\Livewire\Eyepiece;

use App\Models\Set;
use App\Models\Lens;
use Livewire\Component;
use App\Models\Eyepiece;
use App\Models\Instrument;
use Illuminate\Support\Facades\Auth;

class View extends Component
{
    public $instrument;
    public $lens;
    public $equipment;
    public $selected_equipment = 0;

    protected $listeners = [
        'activate'          => 'activate',
        'delete'            => 'delete',
        'instrumentChanged' => 'instrumentChanged',
        'lensChanged'       => 'lensChanged',
    ];

    public function activate($id)
    {
        $eyepiece = Eyepiece::where('id', $id)->first();
        $eyepiece->toggleActive();
        if ($eyepiece->active) {
            session()->flash('message', _i('Eyepiece %s is active', $eyepiece->name));
        } else {
            session()->flash('message', _i('Eyepiece %s is not longer active', $eyepiece->name));
        }
    }

    public function instrumentChanged($id)
    {
        $this->instrument = $id;
        if ($id != 0) {
            $instactive  = Instrument::where('id', $id)->first()->active;
            if ($instactive) {
                Auth::user()->update(['stdtelescope' => $id]);
            }
        } else {
            Auth::user()->update(['stdtelescope' => null]);
            $id = null;
        }
        $this->emit('updateLivewireDatatable', $this->equipment, $id, $this->lens);
    }

    public function lensChanged($id)
    {
        $this->lens = $id;
        if ($id != 0) {
            $lensactive  = Lens::where('id', $id)->first()->active;
            if ($lensactive) {
                Auth::user()->update(['stdlens' => $id]);
            }
        } else {
            Auth::user()->update(['stdlens' => null]);
            $id = null;
        }
        $this->emit('updateLivewireDatatable', $this->equipment, $this->instrument, $id);
    }

    public function delete($id)
    {
        $eyepiece = Eyepiece::where('id', $id)->first();

        if ($eyepiece->observations > 0) {
            session()->flash(
                'message',
                _i(
                    'Eyepiece %s has observations. Impossible to delete.',
                    $eyepiece->name
                )
            );
        } else {
            if (Eyepiece::find($id)->hasMedia('eyepiece')) {
                Eyepiece::find($id)
                    ->getFirstMedia('eyepiece')
                    ->delete();
            }
            $eyepiece->delete();
            $this->emit('refreshLivewireDatatable');

            session()->flash(
                'message',
                _i('Eyepiece %s deleted', $eyepiece->name)
            );
        }
    }

    /**
     * Sets the database values.
     *
     * @return void
     */
    public function mount()
    {
        if (!Auth::guest()) {
            $this->instrument   = Auth::user()->stdtelescope;
            $this->lens         = Auth::user()->stdlens;
        }
    }

    /**
     * Real time validation.
     *
     * @param mixed $propertyName The name of the property
     *
     * @return void
     */
    public function updated($propertyName)
    {
        if ($propertyName == 'equipment') {
            // TODO: 3. Add equipment set to the detail page for targets
            // TODO: 4. Other users should not be able to see the sets
            // TODO: 5. Show all sets for the administrator

            // Check if the instrument is part of the selected equipment -> else deselect the instrument
            // Check if the lens is part of the selected equipment -> else deselect the lens
            if ($this->equipment == -1) {
                $instruments = Instrument::where(
                    ['user_id' => Auth::user()->id]
                )->where(['active' => 1])->pluck('id');
                $lenses = Lens::where(
                    ['user_id' => Auth::user()->id]
                )->where(['active' => 1])->pluck('id');
            } elseif ($this->equipment == 0) {
                $instruments = Instrument::where(
                    ['user_id' => Auth::user()->id]
                )->pluck('id');
                $lenses = Lens::where(
                    ['user_id' => Auth::user()->id]
                )->pluck('id');
            } else {
                $instruments = Set::where('id', $this->equipment)->first()->instruments()->pluck('id');
                $lenses      = Set::where('id', $this->equipment)->first()->lenses()->pluck('id');
            }

            if (!$instruments->contains($this->instrument)) {
                if ($instruments->contains(Auth::user()->stdtelescope)) {
                    $this->instrument = Auth::user()->stdtelescope;
                } else {
                    $this->instrument = 0;
                }
            }

            if (!$lenses->contains($this->lens)) {
                if ($lenses->contains(Auth::user()->stdlens)) {
                    $this->lens = Auth::user()->stdlens;
                } else {
                    $this->lens = 0;
                }
            }

            // Update the list with the eyepieces to only show the eyepieces of the equipment set
            $this->emit('updateLivewireDatatable', $this->equipment, $this->instrument, $this->lens);
        }
    }

    public function render()
    {
        return view('livewire.eyepiece.view');
    }
}
