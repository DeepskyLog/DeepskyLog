<?php

namespace App\Livewire;

use App\Models\InstrumentSet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateInstrumentSet extends Component
{
    use WithFileUploads;

    public $set;

    public $name;

    public $description;

    // Related items
    public $instruments = [];

    public $eyepieces = [];

    public $lenses = [];

    public $filters = [];

    public $locations = [];

    #[Validate('image')]
    public $photo;

    public function mount(): void
    {
        if ($this->set) {
            $this->name = $this->set->name;
            $this->description = $this->set->description;
            // Pre-load related item ids so multi-selects show existing selections when editing
            // Prefix the column with the related table name to avoid ambiguous `id` when pivot tables
            // also have an `id` column (join selects otherwise produce ambiguous column errors).
            $this->instruments = $this->set->instruments()->pluck('instruments.id')->toArray();
            $this->eyepieces = $this->set->eyepieces()->pluck('eyepieces.id')->toArray();
            $this->lenses = $this->set->lenses()->pluck('lenses.id')->toArray();
            $this->filters = $this->set->filters()->pluck('filters.id')->toArray();
            $this->locations = $this->set->locations()->pluck('locations.id')->toArray();
        }
    }

    protected $listeners = ['setDescription'];

    public function setDescription($content)
    {
        $this->description = $content;
    }

    public function render()
    {
        if ($this->set) {
            return view('livewire.create-instrumentset', ['update' => true]);
        }

        return view('livewire.create-instrumentset', ['update' => false]);
    }

    public function save()
    {
        $data = $this->validate([
            'name' => 'required|min:3',
            'photo' => 'nullable|image|max:10240', // max 10MB
            'description' => 'nullable|string',
            'instruments' => 'nullable|array',
            'instruments.*' => 'integer',
            'eyepieces' => 'nullable|array',
            'eyepieces.*' => 'integer',
            'lenses' => 'nullable|array',
            'lenses.*' => 'integer',
            'filters' => 'nullable|array',
            'filters.*' => 'integer',
            'locations' => 'nullable|array',
            'locations.*' => 'integer',
        ], [
            'name.required' => __('Please enter a name for the instrument set.'),
            'name.min' => __('The name must be at least :min characters.', ['min' => 3]),
            'photo.image' => __('The uploaded file must be an image.'),
            'photo.max' => __('The uploaded image may not be larger than :max kilobytes.', ['max' => 10240]),
        ]);

        if ($this->photo) {
            $upload_name = Str::slug(Auth()->user()->slug.' '.$this->name, '-').'.'.$this->photo->getClientOriginalExtension();
            $photoPath = $this->photo->storePubliclyAs('photos/instrumentset', $upload_name, 'public');
            $data['picture'] = $photoPath;
        }

        if ($this->set) {
            $this->set->update($data);

            // Sync relationships
            $this->set->instruments()->sync($this->instruments ?? []);
            $this->set->eyepieces()->sync($this->eyepieces ?? []);
            $this->set->lenses()->sync($this->lenses ?? []);
            $this->set->filters()->sync($this->filters ?? []);
            $this->set->locations()->sync($this->locations ?? []);
            session()->flash('message', __('Instrument set updated successfully.'));

            return redirect('/instrumentset/'.$this->set->user->slug.'/'.$this->set->slug);
        }

        $data['user_id'] = Auth::id();
        $set = InstrumentSet::create($data);

        // Sync relationships
        $set->instruments()->sync($this->instruments ?? []);
        $set->eyepieces()->sync($this->eyepieces ?? []);
        $set->lenses()->sync($this->lenses ?? []);
        $set->filters()->sync($this->filters ?? []);
        $set->locations()->sync($this->locations ?? []);

        session()->flash('message', __('Instrument set created successfully.'));

        return redirect('/instrumentset/'.Auth()->user()->slug.'/'.$set->slug);
    }
}
