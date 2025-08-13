<?php

namespace App\Http\Livewire;

use App\Models\Location;
use Livewire\Component;

class CreateLocation extends Component
{
    public $location;

    public $update = false;

    public $name;

    public $description;

    public $latitude;

    public $longitude;

    public $hidden = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'hidden' => 'boolean',
    ];

    public function mount($location = null)
    {
        if ($location) {
            $this->location = $location;
            $this->update = true;
            $this->name = $location->name;
            $this->description = $location->description;
            $this->latitude = $location->latitude;
            $this->longitude = $location->longitude;
            $this->hidden = $location->hidden;
        }
    }

    public function save()
    {
        $this->validate();
        if ($this->update) {
            $this->location->update([
                'name' => $this->name,
                'description' => $this->description,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'hidden' => $this->hidden,
            ]);
            session()->flash('message', 'Location updated successfully!');
        } else {
            Location::create([
                'name' => $this->name,
                'description' => $this->description,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'hidden' => $this->hidden,
            ]);
            session()->flash('message', 'Location created successfully!');
            $this->reset(['name', 'description', 'latitude', 'longitude', 'hidden']);
        }
    }

    public function render()
    {
        $this->dispatchBrowserEvent('init-map');

        return view('livewire.create-location');
    }
}
