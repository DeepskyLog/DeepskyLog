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

    public $description;

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
            // Only calculate focal ratio if aperture is a positive number
            $ap = floatval($this->aperture_mm);
            $fl = floatval($this->focal_length_mm);
            if ($ap > 0) {
                $this->f_d = round($fl / $ap, 1);
            } else {
                $this->f_d = null;
            }
            $this->fixedMagnification = $this->instrument->fixedMagnification;
            if ($this->instrument->obstruction_perc != 0) {
                $this->obstruction_perc = $this->instrument->obstruction_perc;
            }
            $this->flipped_image = boolval($this->instrument->flip_image);
            $this->flopped_image = boolval($this->instrument->flop_image);
            $this->mount_type_id = $this->instrument->mount_type_id;
            $this->instrument_make = $this->instrument->make_id;
            $this->instrument_type_id = $this->instrument->instrument_type_id;
            $this->description = $this->instrument->description;
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
        // If focal length is already set and aperture becomes valid, compute f/d
        $ap = floatval($this->aperture_mm);
        $fl = floatval($this->focal_length_mm);

        if ($ap > 0 && $fl > 0) {
            $this->f_d = round($fl / $ap, 1);
            return;
        }

        // Fallback: if user updated aperture but f_d and focal length are present,
        // try to update focal length from f_d (existing behaviour)
        $this->updateFocal();
    }

    public function updateFocal(): void
    {
        // Only update focal length if f_d and aperture are valid numbers
        $ap = floatval($this->aperture_mm);
        if ($this->f_d === '' || $this->f_d === null || $ap <= 0) {
            // don't attempt calculation when inputs are missing or invalid
            return;
        }

        $this->focal_length_mm = round(floatval($this->f_d) * $ap, 1);
    }

    public function updateFd(): void
    {
        // Guard against division by zero or empty aperture
        $ap = floatval($this->aperture_mm);
        if ($ap <= 0) {
            $this->f_d = null;
            return;
        }

        $this->f_d = round(floatval($this->focal_length_mm) / $ap, 1);
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
            'description' => 'nullable|string',
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
