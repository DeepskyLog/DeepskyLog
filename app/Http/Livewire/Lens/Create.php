<?php

namespace App\Http\Livewire\Lens;

use App\Models\Lens;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibraryPro\Rules\Concerns\ValidatesMedia;

class Create extends Component
{
    use WithFileUploads;
    use ValidatesMedia;

    public $update;
    public $lens;
    public $sel_lens;
    public $name;
    public $factor;
    public $file = [];

    protected $rules = [
        'name'      => ['required', 'min:6'],
        'factor'    => ['required', 'numeric', 'gt:0', 'lt:10'],
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
        if ($this->lens->exists) {
            $this->update      = true;
            $this->factor      = $this->lens->factor;
            $this->name        = $this->lens->name;
        } else {
            $this->update      = false;
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

        if ($propertyName == 'sel_lens') {
            $this->lens      = \App\Models\Lens::where('id', $this->sel_lens)->first();
            $this->name      = $this->lens->name;
            $this->factor    = $this->lens->factor;
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->update) {
            // Update the existing lens
            $this->lens->update(['name' => $this->name]);
            $this->lens->update(['factor' => $this->factor]);
            $lens = $this->lens;
            laraflash(_i('Lens %s updated', $lens->name))->success();
        } else {
            // Create a new lens
            $lens = Lens::create(
                ['user_id'      => Auth::user()->id,
                    'name'      => $this->name,
                    'factor'    => $this->factor,
                    'active'    => 1, ]
            );
            laraflash(_i('Lens %s created', $lens->name))->success();
        }

        // Upload of the image
        if ($this->file) {
            // $this->validate([
            //     'photo' => 'image|max:10240',
            // ]);
            if (Lens::find($lens->id)->getFirstMedia('lens') != null) {
                // First remove the current image
                Lens::find($lens->id)
                        ->getFirstMedia('lens')
                        ->delete();
            }
            // Update the picture
            Lens::find($lens->id)
                ->addFromMediaLibraryRequest($this->file)
                ->toMediaCollection('lens');
        }

        // View the page with all lenses for the user
        return redirect(route('lens.index'));
    }

    public function render()
    {
        return view('livewire.lens.create');
    }
}
