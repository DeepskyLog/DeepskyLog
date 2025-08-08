<?php

namespace App\Livewire;

use App\Models\Filter;
use App\Models\FilterMake;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateFilter extends Component
{
    use WithFileUploads;

    public $filter;

    public $name;

    public $filter_make;

    public $filter_new_make;

    public $type_id;

    public $color_id;

    public $wratten;

    public $schott;

    #[Validate('image')]
    public $photo;

    public function mount(): void
    {
        if ($this->filter) {
            $this->name = $this->filter->name;

            $this->type_id = $this->filter->type_id;
            $this->filter_make = $this->filter->make_id;
            $this->color_id = $this->filter->color_id;

            $this->wratten = $this->filter->wratten;
            $this->schott = $this->filter->schott;
        }
    }

    public function render()
    {
        if ($this->filter) {
            return view('livewire.create-filter', ['update' => true]);
        }

        return view('livewire.create-filter', ['update' => false]);
    }

    public function save()
    {
        if ($this->filter_make != 1 && $this->filter_make) {
            $make = $this->filter_make;
        } elseif ($this->filter_new_make != '') {
            if (! $this->filter_new_make) {
                return redirect()->back()->withErrors(['filter_new_make' => 'Please select a make or enter a new one']);
            }
            $make_name = $this->filter_new_make;

            // Create a new make
            $make = FilterMake::create(['name' => $make_name])->id;
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
            $photoPath = $this->photo->storePubliclyAs('photos/filter', $upload_name, 'public');
        }

        if ($this->type_id != 7) {
            $this->color_id = 1;
        }
        $data = $this->validate([
            'name' => 'required|min:3',
            'color_id' => 'numeric|min:1',
            'type_id' => 'required|numeric|min:1',
            'wratten' => 'nullable|string|max:25',
            'schott' => 'nullable|string|max:25',
            'photo' => 'nullable|image',
        ]);

        $data['make_id'] = $make;

        if ($this->photo) {
            $data['picture'] = $photoPath;
        }

        if ($this->filter) {
            $this->filter->update($data);
            session()->flash('message', __('Filter updated successfully.'));

            // Return to /filter/{user-slug}/{filter-slug} page
            return redirect('/filter/'.$this->filter->user->slug.'/'.$this->filter->slug);
        } else {
            $data['user_id'] = Auth::id();
            $data['observer'] = Auth::user()->username;
            $filter = Filter::create($data);

            session()->flash('message', __('Filter created successfully.'));

            // Return to /filter/{user-slug}/{filter-slug} page
            return redirect('/filter/'.Auth()->user()->slug.'/'.$filter->slug);
        }
    }
}
