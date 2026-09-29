<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Instrument;
use App\Models\Eyepiece;
use App\Models\Lens;

class MagnificationPicker extends Component
{
    public $instrument_id;
    public $eyepiece_id;
    public $lens_id;
    public $instrumentSetId;

    public $computed_magnification;
    public $manual_magnification;

    public function mount($instrument_id = null, $eyepiece_id = null, $lens_id = null, $instrumentSetId = null, $manual_magnification = null)
    {
        $this->instrument_id = $instrument_id;
        $this->eyepiece_id = $eyepiece_id;
        $this->lens_id = $lens_id;
        $this->instrumentSetId = $instrumentSetId;
        $this->manual_magnification = $manual_magnification;

        // A magnification that was entered (or stored with the observation
        // being edited, e.g. for a zoom eyepiece) is kept; the computed one
        // only fills an empty field.
        $this->recompute(trim((string) $manual_magnification) === '');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['instrument_id', 'eyepiece_id', 'lens_id'], true)) {
            $this->recompute();
        }
    }

    public function recompute(bool $overwrite = true): void
    {
        $this->computed_magnification = null;

        try {
            $inst = !empty($this->instrument_id) ? Instrument::find($this->instrument_id) : null;
            if ($inst) {
                $ep = !empty($this->eyepiece_id) ? Eyepiece::find($this->eyepiece_id) : null;
                $ln = !empty($this->lens_id) ? Lens::find($this->lens_id) : null;
                $this->computed_magnification = $inst->magnificationFor($ep, $ln);

                if ($this->computed_magnification && $overwrite) {
                    $this->manual_magnification = (string) $this->computed_magnification;
                }
            }
        } catch (\Throwable $_) {
            // ignore errors and leave computed null
        }
    }

    public function render()
    {
        return view('livewire.magnification-picker');
    }
}
