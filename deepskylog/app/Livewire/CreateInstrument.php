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

    public $instrument;

    public $name;

    public $instrument_make;

    public $instrument_new_make;

    public $instrument_type_id;

    public $aperture_mm;

    public $focal_length_mm;

    public $f_d;

    public $fixedMagnification;

    public $obstruction_perc;

    public $mount_type_id;

    public $flipped_image;

    public $flopped_image;

    #[Validate('image')]
    public $photo;

    public function mount(): void
    {
        if ($this->instrument) {
            $this->name = $this->instrument->name;

            if (Auth::user()->showInches) {
                $this->aperture_mm = round($this->instrument->aperture_mm / 25.4, 1);
                $this->focal_length_mm = round($this->instrument->focal_length_mm / 25.4, 1);
            } else {
                $this->aperture_mm = $this->instrument->aperture_mm;
                $this->focal_length_mm = $this->instrument->focal_length_mm;
            }
            $this->f_d = round(floatval($this->focal_length_mm) / floatval($this->aperture_mm), 1);
            $this->fixedMagnification = $this->instrument->fixedMagnification;
            if ($this->instrument->obstruction_perc != 0) {
                $this->obstruction_perc = $this->instrument->obstruction_perc;
            }
            $this->flipped_image = boolval($this->instrument->flip_image);
            $this->flopped_image = boolval($this->instrument->flop_image);
            $this->mount_type_id = $this->instrument->mount_type_id;
            $this->instrument_make = $this->instrument->make_id;
            $this->instrument_type_id = $this->instrument->instrument_type_id;
        }
    }

    public function render(): Application|Factory|\Illuminate\Contracts\View\View|View
    {
        if ($this->instrument) {
            return view('livewire.create-instrument', ['update' => true]);
        }

        return view('livewire.create-instrument', ['update' => false]);
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
        if ($this->instrument_make != 1 && $this->instrument_make) {
            $make = $this->instrument_make;
        } elseif ($this->instrument_new_make != '') {
            if (! $this->instrument_new_make) {
                return redirect()->back()->withErrors(['instrument_new_make' => 'Please select a make or enter a new one']);
            }
            $make_name = $this->instrument_new_make;

            // Create a new make
            $make = InstrumentMake::create(['name' => $make_name])->id;
        } else {
            $make = 1;
        }

        $photoPath = null;
        if ($this->photo) {
            $upload_name = Str::slug(
                Auth()->user()->slug.' '.$make.' '.$this->name,
                '-'
            ).'.'.$this->photo->getClientOriginalExtension();
            // Make a slug from the upload_name
            $photoPath = $this->photo->storePubliclyAs('photos/instruments', $upload_name, 'public');
        }

        if ($this->focal_length_mm == '') {
            $this->focal_length_mm = 0;
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
            'focal_length_mm' => 'numeric|min:0|nullable',
            'f_d' => 'numeric|min:0|nullable',
            'fixedMagnification' => 'nullable|numeric|min:1',
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

        if ($this->photo) {
            $data['picture'] = $photoPath;
        }

        if ($this->instrument) {
            $this->instrument->update($data);
            session()->flash('message', __('Instrument updated successfully.'));

            // Return to /instrument/{user-slug}/{instrument-slug} page
            return redirect('/instrument/'.$this->instrument->user->slug.'/'.$this->instrument->slug);
        } else {
            $data['user_id'] = Auth::id();
            $data['observer'] = Auth::user()->username;
            $instrument = Instrument::create($data);

            session()->flash('message', __('Instrument created successfully.'));

            // Return to /instrument/{user-slug}/{instrument-slug} page
            return redirect('/instrument/'.Auth()->user()->slug.'/'.$instrument->slug);
        }

    }
}
