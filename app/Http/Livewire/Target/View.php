<?php

namespace App\Http\Livewire\Target;

use Livewire\Component;
use App\Models\Astrolib;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

class View extends Component
{
    public $location;
    public $instrument;
    public $targetsToShow;

    public function render()
    {
        return view('livewire.target.view');
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
        if ($propertyName == 'instrument') {
            Auth::user()->update(['stdtelescope' => $this->instrument]);
            $this->emit('instrumentChanged');
        }
        if ($propertyName == 'location') {
            Auth::user()->update(['stdlocation' => $this->location]);
            $location = Location::where('id', $this->location)->first();
            $coords   = new GeographicalCoordinates($location->longitude, $location->latitude);
            Astrolib::getInstance()->getAstronomyLibrary()->setGeographicalCoordinates($coords);
            Astrolib::getInstance()->setLocation($location);
            $this->emit('locationChanged');
        }
    }
}
