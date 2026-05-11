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

        $this->recompute();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['instrument_id', 'eyepiece_id', 'lens_id'], true)) {
            $this->recompute();
        }
    }

    public function recompute(): void
    {
        $this->computed_magnification = null;

        try {
            if (!empty($this->instrument_id) && !empty($this->eyepiece_id)) {
                $inst = Instrument::find($this->instrument_id);
                $ep = Eyepiece::find($this->eyepiece_id);
                $ln = $this->lens_id ? Lens::find($this->lens_id) : null;

                if ($inst && $ep) {
                    $val = $inst->magnification($ep, $ln); // e.g. '200x'
                    $num = preg_replace('/[^0-9\.]/', '', (string) $val);
                    $this->computed_magnification = $num !== '' ? (float) $num : null;

                    if ($this->computed_magnification) {
                        $this->manual_magnification = (string) round($this->computed_magnification);
                    }
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
