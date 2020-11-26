<?php

namespace App\Http\Livewire\Target;

use App\Models\Target;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Detail extends Component
{
    public Target $object;
    public $instrument;
    public $instrument2;
    public $location;
    public $eyepiece;
    public $lens;
    public $disabled;

    protected $listeners = ['dateChanged' => 'dateChanged'];

    public function dateChanged()
    {
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
            $this->instrument2  = Auth::user()->stdtelescope;
            $this->location     = Auth::user()->stdlocation;
            $this->eyepiece     = Auth::user()->stdeyepiece;
            $this->lens         = Auth::user()->stdlens;

            $this->disabled = true;

            if ($this->instrument) {
                if (\App\Models\Instrument::where('id', $this->instrument)->first()->fd) {
                    $this->disabled = false;
                }
            }
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
        if ($propertyName == 'instrument') {
            Auth::user()->update(['stdtelescope' => $this->instrument]);
            $this->emit('updateFov', $this->object->getFOV());
            if (!\App\Models\Instrument::where('id', $this->instrument)->first()->fd) {
                $this->disabled = true;
            } else {
                $this->disabled = false;
            }
            $this->instrument2 = $this->instrument;
        }
        if ($propertyName == 'instrument2') {
            Auth::user()->update(['stdtelescope' => $this->instrument2]);
            $this->emit('updateFov', $this->object->getFOV());
            if (!\App\Models\Instrument::where('id', $this->instrument2)->first()->fd) {
                $this->disabled = true;
            } else {
                $this->disabled = false;
            }
            $this->instrument = $this->instrument2;
        }
        if ($propertyName == 'location') {
            Auth::user()->update(['stdlocation' => $this->location]);
        }
        if ($propertyName == 'eyepiece') {
            if ($this->eyepiece == 0) {
                Auth::user()->update(['stdeyepiece' => null]);
            } else {
                Auth::user()->update(['stdeyepiece' => $this->eyepiece]);
            }
            $this->emit('updateFov', $this->object->getFOV());
        }
        if ($propertyName == 'lens') {
            if ($this->lens == 0) {
                Auth::user()->update(['stdlens' => null]);
            } else {
                Auth::user()->update(['stdlens' => $this->lens]);
            }
            $this->emit('updateFov', $this->object->getFOV());
        }
    }

    public function render()
    {
        return view('livewire.target.detail', ['target' => $this->object]);
    }
}
