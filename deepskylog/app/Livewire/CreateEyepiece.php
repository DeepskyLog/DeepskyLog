<?php

namespace App\Livewire;

use App\Models\Eyepiece;
use App\Models\EyepieceMake;
use App\Models\EyepieceType;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateEyepiece extends Component
{
    use WithFileUploads;

    public $eyepiece;

    public $name;

    public $eyepiece_make;

    public $eyepiece_new_make;

    public $eyepiece_type;

    public $eyepiece_new_type;

    public $proposed_name;

    public $focal_length_mm;

    public $max_focal_length_mm;

    public $field_stop_mm;

    public $apparentFOV;

    #[Validate('image')]
    public $photo;

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

            if ($this->eyepiece->field_stop_mm > 0) {
                $this->field_stop_mm = $this->eyepiece->field_stop_mm;
            }
        }
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
        $this->proposed_name = '';
        if ($this->eyepiece_make == null || $this->eyepiece_make == 1) {
            $make = $this->eyepiece_new_make;
        } else {
            $make = EyepieceMake::find($this->eyepiece_make)->name;
        }
        if ($this->eyepiece_type == null || $this->eyepiece_type == 1) {
            $type = $this->eyepiece_new_type;
        } else {
            $type = EyepieceType::find($this->eyepiece_type)->name;
        }

        if ($this->max_focal_length_mm > 0) {
            $fl = $this->focal_length_mm.'-'.$this->max_focal_length_mm;
        } else {
            $fl = $this->focal_length_mm;
        }

        if ($make) {
            $this->proposed_name .= $make.' ';
        }
        if ($this->focal_length_mm) {
            $this->proposed_name .= $fl.'mm ';
        }
        if ($type) {
            $this->proposed_name .= $type;
        }

        // Only put proposed name in the name field if a new eyepiece is being created
        if (! $this->eyepiece) {
            $this->name = $this->proposed_name;
        }
    }

    public function updateType(): void
    {
        $this->updateProposedName();
    }

    public function updateFocalLength(): void
    {
        $this->updateProposedName();
    }

    public function save()
    {
        // Eyepiece Make
        if ($this->eyepiece_make != 1 && $this->eyepiece_make) {
            $make = $this->eyepiece_make;
        } elseif ($this->eyepiece_new_make != '') {
            if (! $this->eyepiece_new_make) {
                return redirect()->back()->withErrors(['eyepiece_new_make' => 'Please select a make or enter a new one']);
            }
            $make_name = $this->eyepiece_new_make;

            // Create a new make
            $make = EyepieceMake::create(['name' => $make_name])->id;
        } else {
            $make = 1;
        }

        // Eyepiece Type
        if ($this->eyepiece_type != 1 && $this->eyepiece_type) {
            $type = $this->eyepiece_type;
        } elseif ($this->eyepiece_new_type != '') {
            if (! $this->eyepiece_new_type) {
                return redirect()->back()->withErrors(['eyepiece_new_type' => 'Please select a type or enter a new one']);
            }
            $type_name = $this->eyepiece_new_type;

            // Create a new type
            $type = EyepieceType::create(['name' => $type_name, 'eyepiece_makes_id' => $make])->id;
        } else {
            $type = 1;
        }

        $photoPath = null;
        if ($this->photo) {
            $upload_name = Str::slug(
                Auth()->user()->slug.' '.$this->name,
                '-'
            ).'.'.$this->photo->getClientOriginalExtension();
            // Make a slug from the upload_name
            $photoPath = $this->photo->storePubliclyAs('photos/eyepieces', $upload_name, 'public');
        }

        $data = $this->validate([
            'name' => 'required|min:3',
            'focal_length_mm' => 'numeric|min:1',
            'apparentFOV' => 'numeric|min:1|max:150|nullable',
            'field_stop_mm' => 'numeric|min:1|max:150|nullable',
            'photo' => 'nullable|image',
        ]);

        if ($this->field_stop_mm == '') {
            $data['field_stop_mm'] = 0;
        }

        if ($this->max_focal_length_mm == '') {
            $data['max_focal_length_mm'] = -1;
        }

        $data['make_id'] = $make;
        $data['type_id'] = $type;
        $data['user_id'] = Auth::id();
        $data['observer'] = Auth::user()->username;

        if ($this->photo) {
            $data['picture'] = $photoPath;
        }

        if ($this->eyepiece) {
            $this->eyepiece->update($data);
            session()->flash('message', __('Eyepiece updated successfully.'));

            // Return to /eyepiece/{user-slug}/{eyepiece-slug} page
            return redirect('/eyepiece/'.Auth()->user()->slug.'/'.$this->eyepiece->slug);
        } else {
            $eyepiece = Eyepiece::create($data);

            session()->flash('message', __('Eyepiece created successfully.'));

            // Return to /eyepiece/{user-slug}/{eyepiece-slug} page
            return redirect('/eyepiece/'.Auth()->user()->slug.'/'.$eyepiece->slug);
        }
    }
}
