<?php

namespace App\Livewire;

use App\Models\Location;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\View\View;
use Livewire\Component;

class CreateLocation extends Component
{
    public $location;

    public $update = false;

    public $name;

    public $description;

    public $latitude;

    public $longitude;

    public $elevation;

    public $hidden = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'hidden' => 'boolean',
        'elevation' => 'nullable|numeric',
    ];

    public function mount($location = null): void
    {
        if ($location) {
            $this->location = $location;
            $this->update = true;
            $this->name = $location->name;
            $this->description = $location->description;
            $this->latitude = $location->latitude;
            $this->longitude = $location->longitude;
            $this->hidden = $location->hidden;
            $this->elevation = $location->elevation;
        }
    }

    public function save(): void
    {
        $this->validate();
        if ($this->update) {
            $this->location->update([
                'name' => $this->name,
                'description' => $this->description,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'hidden' => $this->hidden,
                'elevation' => $this->elevation,
            ]);
            session()->flash('message', 'Location updated successfully!');
        } else {
            Location::create([
                'name' => $this->name,
                'description' => $this->description,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'hidden' => $this->hidden,
                'elevation' => $this->elevation,
            ]);
            session()->flash('message', 'Location created successfully!');
            $this->reset(['name', 'description', 'latitude', 'longitude', 'hidden']);
        }
    }

    public function updateElevation($latitude, $longitude): void
    {
        $client = new Client;
        try {
            $response = $client->get('https://api.opentopodata.org/v1/mapzen', [
                'query' => [
                    'locations' => "$latitude,$longitude",
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            // Seems to work fine: But
            // To keep the public API sustainable some limitations are applied.
            //
            // Max 100 locations per request.
            // Max 1 call per second.
            // Max 1000 calls per day.

            // Should work: Need to make sure to only get the elevation when saving the data.
            // Can be hosted on our own server if needed: See https://www.opentopodata.org/#public-api
            $this->elevation = $data['results'][0]['elevation'] ?? 0;
        } catch (GuzzleException) {
            $this->elevation = 0;
        }
    }

    public function render(): \Illuminate\Contracts\View\View|Application|Factory|View
    {
        return view('livewire.create-location');
    }
}
