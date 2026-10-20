<?php

namespace App\Livewire;

use App\Models\Filter;
use App\Models\FilterColor;
use App\Models\FilterMake;
use App\Models\FilterType;
use App\Support\UploadedPhoto;
use Illuminate\Support\Facades\Auth;
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

    public $description;

    public $proposed_name;

    public function mount(): void
    {
        if ($this->filter) {
            abort_unless($this->filter->canBeEditedBy(Auth::user()), 403);

            $this->name = $this->filter->name;

            $this->type_id = $this->filter->type_id;
            $this->filter_make = $this->filter->make_id;
            $this->color_id = $this->filter->color_id;

            $this->wratten = $this->filter->wratten;
            $this->schott = $this->filter->schott;
            $this->description = $this->filter->description;
        }
    }

    public function render()
    {
        if ($this->filter) {
            return view('livewire.create-filter', ['update' => true]);
        }

        return view('livewire.create-filter', ['update' => false]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['filter_make', 'filter_new_make', 'type_id', 'color_id', 'wratten', 'schott'])) {
            $this->updateProposedName();
        }
    }

    public function updateProposedName(): void
    {
        if ($this->filter_make == null || $this->filter_make == 1) {
            $make = trim((string) $this->filter_new_make);
        } else {
            $make = FilterMake::find($this->filter_make)?->name;
        }

        // A color filter is named after its color and Wratten or Schott number, other filters after their type
        $parts = [$make];
        if ($this->type_id == 7) {
            if ($this->color_id && $this->color_id != 1) {
                $parts[] = FilterColor::find($this->color_id)?->name;
            }
            if (trim((string) $this->wratten) != '') {
                $parts[] = 'Wratten '.trim($this->wratten);
            } elseif (trim((string) $this->schott) != '') {
                $parts[] = 'Schott '.trim($this->schott);
            }
        } elseif ($this->type_id && $this->type_id != 1) {
            $parts[] = FilterType::find($this->type_id)?->name;
        }

        $this->proposed_name = implode(' ', array_filter($parts));

        // Only put the proposed name in the name field if a new filter is being created
        if (! $this->filter) {
            $this->name = $this->proposed_name;
        }
    }

    public function save()
    {
        if ($this->filter) {
            abort_unless($this->filter->canBeEditedBy(Auth::user()), 403);
        }

        if ($this->filter_make != 1 && $this->filter_make) {
            $make = $this->filter_make;
        } elseif ($this->filter_new_make != '') {
            if (! $this->filter_new_make) {
                return redirect()->back()->withErrors(['filter_new_make' => 'Please select a make or enter a new one']);
            }
            $make_name = $this->filter_new_make;

            // Create a new make
            $make = FilterMake::firstOrCreate(['name' => trim($make_name)])->id;
        } else {
            $make = 1;
        }

        if ($this->type_id != 7) {
            $this->color_id = 1;
        }
        $data = $this->validate([
            'name' => 'required|min:3',
            'color_id' => 'numeric|min:1',
            'type_id' => 'required|numeric|min:1',
            'wratten' => 'nullable|string|max:5',
            'schott' => 'nullable|string|max:5',
            'photo' => 'nullable|image',
            'description' => 'nullable|string',
        ]);

        $photoPath = null;
        if ($this->photo) {
            $photoPath = UploadedPhoto::store($this->photo, 'photos/filter', $this->filter);
        }

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
