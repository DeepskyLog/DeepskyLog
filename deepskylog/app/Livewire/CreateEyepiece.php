<?php

namespace App\Livewire;

use App\Models\EyepieceMake;
use App\Models\EyepieceType;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateEyepiece extends Component
{
    use WithFileUploads;

    public $eyepiece;

    public $name;

    public $eyepiece_make;

    public $eyepiece_type;

    public $proposed_name;

    public $focal_length_mm;

    public $max_focal_length_mm;

    public $apparentFOV;

    public function mount(): void
    {
        if ($this->eyepiece) {
            $this->name = $this->eyepiece->name;

            $this->eyepiece_make = $this->eyepiece->make_id;

            $this->eyepiece_type = $this->eyepiece->type_id;

            $this->focal_length_mm = $this->eyepiece->focal_length_mm;

            if ($this->eyepiece->max_focal_length_mm > 0) {
                $this->max_focal_length_mm = $this->eyepiece->max_focal_length_mm;
            }

            if ($this->eyepiece->apparentFOV > 0) {
                $this->apparentFOV = $this->eyepiece->apparentFOV;
            }
        }

        // TODO: Add missing input fields
        // TODO: If make is selected, disable the new make input
        // TODO: If type is selected, disable the new type input
        // TODO: If new eyepiece is added, adapt the name automatically to the proposed name
        // TODO: Add the Save button
        // TODO: Write the Save method
    }

    public function render(): Application|Factory|\Illuminate\Contracts\View\View|View
    {
        if ($this->eyepiece) {
            return view('livewire.create-eyepiece', ['update' => true]);
        }

        return view('livewire.create-eyepiece', ['update' => false]);
    }

    public function updateMake(): void
    {
        $this->updateProposedName();
    }

    public function updateProposedName(): void
    {
        $make = EyepieceMake::find($this->eyepiece_make);
        $type = EyepieceType::find($this->eyepiece_type);

        if ($this->max_focal_length_mm > 0) {
            $fl = $this->focal_length_mm.'-'.$this->max_focal_length_mm;
        } else {
            $fl = $this->focal_length_mm;
        }
        $this->proposed_name = $make->name.' '.$fl.'mm '.$type->name;
    }

    public function updateType(): void
    {
        $this->updateProposedName();
    }

    public function updateFocalLength(): void
    {
        $this->updateProposedName();
    }
}
