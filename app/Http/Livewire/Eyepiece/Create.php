<?php

namespace App\Http\Livewire\Eyepiece;

use Livewire\Component;
use App\Models\Eyepiece;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibraryPro\Rules\Concerns\ValidatesMedia;

class Create extends Component
{
    use WithFileUploads;
    use ValidatesMedia;

    public $eyepiece;
    public $sel_eyepiece;
    public $update;
    public $name;
    public $focalLength;
    public $genericName;
    public $maxFocalLength;
    public $brand;
    public $type;
    public $apparentFov;
    public $newBrand;
    public $allTypes;
    public $newType;
    public $file = [];

    protected $rules = [
        'name'           => ['required', 'min:6'],
        'newBrand'       => 'required_without:brand|min:3',
        'brand'          => 'required_without:newBrand',
        'newType'        => 'required_without:type|min:3',
        'type'           => 'required_without:newType',
        'focalLength'    => 'required|numeric|gte:1|lte:99',
        'apparentFov'    => 'required|numeric|gte:20|lte:150',
        'maxFocalLength' => 'nullable|gte:1|lte:99',
    ];

    protected $listeners = [
        'mediaChanged',
    ];

    public function mediaChanged($media)
    {
        $this->file = $media;
    }

    public function mount()
    {
        if ($this->eyepiece->exists) {
            $this->update      = true;
            $this->name        = $this->eyepiece->name;
            $this->focalLength = $this->eyepiece->focalLength;
            $this->apparentFov = $this->eyepiece->apparentFOV;
            $this->brand       = $this->eyepiece->brand;
            $this->allTypes    = \App\Models\EyepieceType::where('brand', $this->eyepiece->brand)->pluck('type', 'type')->toArray();
            $this->type        = $this->eyepiece->type;
            $this->newBrand    = '';
            $this->newType     = '';
            $this->updateGenericname();
        } else {
            $this->update      = false;
            $this->allTypes    = [0 => ''];
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
        $this->validateOnly($propertyName);

        if ($propertyName == 'sel_eyepiece') {
            $this->eyepiece    = \App\Models\Eyepiece::where('id', $this->sel_eyepiece)->first();
            $this->name        = $this->eyepiece->name;
            $this->focalLength = $this->eyepiece->focalLength;
            $this->apparentFov = $this->eyepiece->apparentFOV;
            $this->brand       = $this->eyepiece->brand;
            $this->allTypes    = \App\Models\EyepieceType::where('brand', $this->eyepiece->brand)->pluck('type', 'type')->toArray();
            $this->type        = $this->eyepiece->type;
            $this->newBrand    = '';
            $this->newType     = '';
        }

        if ($propertyName == 'newBrand') {
            $this->brand       = '';
            $this->allTypes    = [0 => ''];
        }

        if ($propertyName == 'brand') {
            $this->newBrand    = '';
            $this->allTypes    = \App\Models\EyepieceType::where('brand', $this->brand)->pluck('type', 'type')->toArray();
            $this->type        = '';
        }

        if ($propertyName == 'newType') {
            $this->type       = '';
        }

        if ($propertyName == 'type') {
            $this->newType     = '';
        }
        $this->updateGenericname();
    }

    /**
     * Updates the generic name of the eyepiece.
     */
    public function updateGenericname()
    {
        if ($this->newBrand != '') {
            $brand = $this->newBrand;
        } else {
            $brand = $this->brand;
        }
        if ($this->newType != '') {
            $type = $this->newType;
        } else {
            $type = $this->type;
        }
        if ($this->maxFocalLength != '') {
            $this->genericName = $this->focalLength . '-' . $this->maxFocalLength . 'mm '
                    . $brand . ' ' . $type;
        } else {
            $this->genericName = $this->focalLength . 'mm ' . $brand . ' ' . $type;
        }
    }

    public function save()
    {
        $this->validate();

        // Check if the brand is new
        if ($this->brand == '') {
            if (\App\Models\EyepieceBrand::where('brand', $this->newBrand)->get()->isEmpty()) {
                \App\Models\EyepieceBrand::create(
                    [
                        'brand' => $this->newBrand,
                    ]
                );
            }
            $this->brand = $this->newBrand;
        }

        // Check if the type is new
        if ($this->type == '') {
            if (\App\Models\EyepieceType::where('brand', $this->brand)->where('type', $this->newType)->get()->isEmpty()) {
                \App\Models\EyepieceType::create(
                    [
                        'brand' => $this->brand,
                        'type'  => $this->newType,
                    ]
                );
            }
            $this->type = $this->newType;
        }

        if ($this->update) {
            // Update the existing eyepiece
            $this->eyepiece->update(['name' => $this->name]);
            $this->eyepiece->update(['brand' => $this->brand]);
            $this->eyepiece->update(['type' => $this->type]);
            $this->eyepiece->update(['focalLength' => $this->focalLength]);
            $this->eyepiece->update(['apparentFOV' => $this->apparentFov]);
            if ($this->maxFocalLength == '') {
                $this->eyepiece->update(['maxFocalLength' => null]);
            } else {
                $this->eyepiece->update(['maxFocalLength' => $this->maxFocalLength]);
            }
            $eyepiece = $this->eyepiece;
            laraflash(_i('Eyepiece %s updated', $eyepiece->name))->success();
        } else {
            if ($this->maxFocalLength == '') {
                $maxFocalLength = null;
            } else {
                $maxFocalLength = $this->maxFocalLength;
            }
            // Create a new eyepiece
            $eyepiece = Eyepiece::create(
                ['user_id'             => Auth::user()->id,
                    'name'             => $this->name,
                    'brand'            => $this->brand,
                    'type'             => $this->type,
                    'focalLength'      => $this->focalLength,
                    'apparentFOV'      => $this->apparentFov,
                    'maxFocalLength'   => $maxFocalLength,
                    'active'           => 1, ]
            );
            laraflash(_i('Eyepiece %s created', $eyepiece->name))->success();
        }

        // Upload of the image
        if ($this->file) {
            // $this->validate([
            //     'photo' => 'image|max:10240',
            // ]);
            if (Eyepiece::find($eyepiece->id)->getFirstMedia('eyepiece') != null) {
                // First remove the current image
                Eyepiece::find($eyepiece->id)
                        ->getFirstMedia('eyepiece')
                        ->delete();
            }
            // Update the picture
            Eyepiece::find($eyepiece->id)
                ->addFromMediaLibraryRequest($this->file)
                ->toMediaCollection('eyepiece');
        }

        // View the page with all eyepieces for the user
        return redirect(route('eyepiece.index'));
    }

    public function render()
    {
        return view('livewire.eyepiece.create');
    }
}
