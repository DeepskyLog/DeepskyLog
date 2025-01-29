<?php

namespace App\Livewire;

use App\Models\Instrument;
use App\Models\InstrumentMake;
use App\Models\InstrumentType;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateInstrument extends Component
{
    use WithFileUploads;

    public $name;

    public $instrument_make;

    public $instrument_new_make;

    public $instrument_type_id;

    public $aperture_mm;

    public $focal_length_mm;

    public $f_d;

    public $fixed_mag;

    public $obstruction_perc;

    public $mount_type_id;

    public $flipped_image;

    public $flopped_image;

    #[Validate('image')]
    public $photo;

    public function render(): Application|Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.create-instrument');
    }

    public function updateAperture(): void
    {
        $this->updateFocal();
    }

    public function updateFocal(): void
    {
        $this->focal_length_mm = round(floatval($this->f_d) * floatval($this->aperture_mm), 1);
    }

    public function updateFd(): void
    {
        $this->f_d = round(floatval($this->focal_length_mm) / floatval($this->aperture_mm), 1);
    }

    public function updateFlipFlop(): void
    {
        $instrument_type = InstrumentType::where('id', $this->instrument_type_id)->first();
        $this->flipped_image = boolval($instrument_type->flip_image);
        $this->flopped_image = boolval($instrument_type->flop_image);
    }

    public function save()
    {
        $photoPath = null;
        if ($this->photo) {
            if ($this->instrument_make != 1 && $this->instrument_make) {
                $make = InstrumentMake::where('id', $this->instrument_make)->first()->name;
            } elseif ($this->instrument_new_make) {
                $make = $this->instrument_new_make;
            } else {
                $make = '';
            }
            $upload_name = Str::slug(
                Auth()->user()->slug.' '.$make.' '.$this->name,
                '-'
            ).'.'.$this->photo->getClientOriginalExtension();
            // Make a slug from the upload_name
            $photoPath = $this->photo->storePubliclyAs('photos/instruments', $upload_name, 'public');
        }

        if ($this->instrument_make) {
            $make = $this->instrument_make;
        } else {
            if (! $this->instrument_new_make) {
                return redirect()->back()->withErrors(['instrument_new_make' => 'Please select a make or enter a new one']);
            }
            $make_name = $this->instrument_new_make;

            // Create a new make
            $make = InstrumentMake::create(['name' => $make_name])->id;
        }

        // Check if aperture and focal length are set in inches
        if (Auth::user()->showInches) {
            $this->aperture_mm = $this->aperture_mm * 25.4;
            $this->focal_length_mm = $this->focal_length_mm * 25.4;
        }

        $data = $this->validate([
            'name' => 'required|min:3',
            'instrument_type_id' => 'required',
            'aperture_mm' => 'required|numeric|min:0',
            'focal_length_mm' => 'nullable|numeric|min:0|nullable',
            'f_d' => 'nullable|numeric|min:0|nullable',
            'fixed_mag' => 'nullable|numeric|min:1',
            'mount_type_id' => 'required',
            'obstruction_perc' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image',
        ]);

        if ($this->flipped_image) {
            $data['flip_image'] = true;
        } else {
            $data['flip_image'] = false;
        }

        if ($this->flopped_image) {
            $data['flop_image'] = true;
        } else {
            $data['flop_image'] = false;
        }

        $data['make_id'] = $make;
        $data['user_id'] = Auth::id();
        $data['observer'] = Auth::user()->username;

        if ($this->photo) {
            $data['picture'] = $photoPath;
        }

        $instrument = Instrument::create($data);

        session()->flash('message', _('Instrument created successfully.'));

        // Return to /instrument/{id} page
        return redirect()->route('instrument.show', ['instrument' => $instrument->id]);
    }
}
