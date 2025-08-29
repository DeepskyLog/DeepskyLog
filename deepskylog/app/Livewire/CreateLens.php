<?php

namespace App\Livewire;

use App\Models\Lens;
use App\Models\LensMake;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateLens extends Component
{
    use WithFileUploads;

    public $lens;

    public $name;

    public $lens_make;

    public $lens_new_make;

    public $factor;

    public $description;

    #[Validate('image')]
    public $photo;

    public function mount(): void
    {
        if ($this->lens) {
            $this->name = $this->lens->name;

            $this->factor = floatval($this->lens->factor);
            $this->lens_make = $this->lens->make_id;
        }
    }

    public function render()
    {
        if ($this->lens) {
            return view('livewire.create-lens', ['update' => true]);
        }

        return view('livewire.create-lens', ['update' => false]);
    }

    public function save()
    {
        if ($this->lens_make != 1 && $this->lens_make) {
            $make = $this->lens_make;
        } elseif ($this->lens_new_make != '') {
            if (! $this->lens_new_make) {
                return redirect()->back()->withErrors(['lens_new_make' => 'Please select a make or enter a new one']);
            }
            $make_name = $this->lens_new_make;

            // Create a new make
            $make = LensMake::create(['name' => $make_name])->id;
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
            $photoPath = $this->photo->storePubliclyAs('photos/lens', $upload_name, 'public');
        }

        $data = $this->validate([
            'name' => 'required|min:3',
            'factor' => 'required|numeric|min:0',
            'photo' => 'nullable|image',
            'description' => 'nullable|string',
        ]);

        $data['make_id'] = $make;

        if ($this->photo) {
            $data['picture'] = $photoPath;
        }

        if ($this->lens) {
            $this->lens->update($data);
            session()->flash('message', __('Lens updated successfully.'));

            // Return to /lens/{user-slug}/{lens-slug} page
            return redirect('/lens/'.$this->lens->user->slug.'/'.$this->lens->slug);
        } else {
            $data['user_id'] = Auth::id();
            $data['observer'] = Auth::user()->username;
            $lens = Lens::create($data);

            session()->flash('message', __('Lens created successfully.'));

            // Return to /lens/{user-slug}/{lens-slug} page
            return redirect('/lens/'.Auth()->user()->slug.'/'.$lens->slug);
        }

    }
}
