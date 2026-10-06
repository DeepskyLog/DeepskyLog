<?php

namespace App\Livewire;

use App\Models\Instrument;
use App\Models\InstrumentMake;
use App\Models\InstrumentType;
use App\Models\Lens;
use App\Support\UploadedPhoto;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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

    public $field_of_view_deg;

    public $finder_id;

    public $lens_id;

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
            abort_unless($this->instrument->canBeEditedBy(Auth::user()), 403);

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
            $this->field_of_view_deg = $this->instrument->field_of_view_deg;
            $this->finder_id = $this->instrument->finder_id;
            $this->lens_id = $this->instrument->lens_id;
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
        return view('livewire.create-instrument', [
            'update' => (bool) $this->instrument,
            'finders' => $this->finderOptions(),
            'lenses' => Lens::where('user_id', Auth::id())->orderBy('name')->get()
                ->map(fn (Lens $lens) => ['id' => $lens->id, 'name' => html_entity_decode($lens->name)])
                ->all(),
        ]);
    }

    /**
     * The instruments of the observer that can be the finder of this one:
     * all the others, the ones with a fixed magnification (finders,
     * binoculars) first.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function finderOptions(): array
    {
        return Instrument::where('user_id', Auth::id())
            ->when($this->instrument, fn ($query) => $query->where('id', '!=', $this->instrument->id))
            ->orderByRaw('CASE WHEN fixedMagnification > 0 THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->get()
            ->map(fn (Instrument $instrument) => [
                'id' => $instrument->id,
                'name' => html_entity_decode($instrument->fullName()),
            ])
            ->all();
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
        if ($this->instrument) {
            abort_unless($this->instrument->canBeEditedBy(Auth::user()), 403);
        }

        if ($this->instrument_make != 1 && $this->instrument_make) {
            $make = $this->instrument_make;
        } elseif ($this->instrument_new_make != '') {
            if (! $this->instrument_new_make) {
                return redirect()->back()->withErrors(['instrument_new_make' => 'Please select a make or enter a new one']);
            }
            $make_name = $this->instrument_new_make;

            // Create a new make
            $make = InstrumentMake::firstOrCreate(['name' => trim($make_name)])->id;
        } else {
            $make = 1;
        }

        if ($this->focal_length_mm == '') {
            $this->focal_length_mm = 0;
        }
        if ($this->field_of_view_deg === '') {
            $this->field_of_view_deg = null;
        }
        if ($this->finder_id === '') {
            $this->finder_id = null;
        }
        if ($this->lens_id === '') {
            $this->lens_id = null;
        }

        // The form values are validated; in inches they are converted afterwards,
        // so the form keeps showing inches after a validation error.
        $data = $this->validate([
            'name' => 'required|min:3',
            'instrument_type_id' => 'required',
            'aperture_mm' => 'required|numeric|min:0',
            'focal_length_mm' => 'numeric|min:0|nullable',
            'f_d' => 'numeric|min:0|nullable',
            'fixedMagnification' => 'nullable|numeric|min:1',
            'field_of_view_deg' => 'nullable|numeric|gt:0|max:60',
            'finder_id' => [
                'nullable',
                'integer',
                Rule::exists('instruments', 'id')->where('user_id', Auth::id()),
                Rule::notIn($this->instrument ? [$this->instrument->id] : []),
            ],
            'lens_id' => ['nullable', 'integer', Rule::exists('lenses', 'id')->where('user_id', Auth::id())],
            'mount_type_id' => 'required',
            'obstruction_perc' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image',
            'description' => 'nullable|string',
        ]);

        $photoPath = null;
        if ($this->photo) {
            $photoPath = UploadedPhoto::store($this->photo, 'photos/instruments', $this->instrument);
        }

        // Check if aperture and focal length are set in inches
        if (Auth::user()->showInches) {
            $data['aperture_mm'] = (int) round($this->inchesToMm($data['aperture_mm'], $this->instrument?->aperture_mm));
            $data['focal_length_mm'] = $this->inchesToMm($data['focal_length_mm'] ?? 0, $this->instrument?->focal_length_mm);
        }

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

    /**
     * Converts a value in inches to millimeters. When the value is the one mount()
     * showed for the stored millimeters (rounded to 0.1"), the stored value is
     * kept, so saving without changes doesn't shift it.
     */
    private function inchesToMm($inches, $storedMm): float
    {
        if ($storedMm !== null && round($storedMm / 25.4, 1) == (float) $inches) {
            return (float) $storedMm;
        }

        return (float) $inches * 25.4;
    }
}
