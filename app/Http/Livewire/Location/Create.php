<?php

namespace App\Http\Livewire\Location;

use Livewire\Component;
use App\Models\Location;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\MagnitudeController;
use Spatie\MediaLibraryPro\Rules\Concerns\ValidatesMedia;

class Create extends Component
{
    use WithFileUploads;
    use ValidatesMedia;

    public $name;
    public $location;
    public $latitude  = 0;
    public $longitude = 0;
    public $country   = 'CL';
    public $timezone;
    public $elevation;
    public $update;
    public $limitingMagnitude;
    public $bortle;
    public $skyBackground;
    public $file = [];

    protected $rules = [
        'name'                               => 'required|min:3',
        'latitude'                           => 'required|numeric|lte:90|gte:-90',
        'longitude'                          => 'required|numeric|lte:180|gte:-180',
        'country'                            => 'required',
        'elevation'                          => 'required|numeric|lte:8888|gte:-200',
        'timezone'                           => 'required|timezone',
        'limitingMagnitude'                  => 'nullable|numeric|lte:8.0|gte:-1.0',
        'skyBackground'                      => 'nullable|numeric|lte:22.0|gte:10.0',
        'bortle'                             => 'nullable|numeric|lte:9|gte:1',
        // 'photo' => [$this->validateSingleMedia()->maxItemSizeInKb(10000)]
    ];

    protected $listeners = [
        'latitude'    => 'setLatitude',
        'longitude'   => 'setLongitude',
        'country'     => 'setCountry',
        'timezone'    => 'setTimezone',
        'elevation'   => 'setElevation',
        'mediaChanged',
    ];

    public function mediaChanged($media)
    {
        $this->file = $media;
    }

    /**
     * Listener for the latitude event
     *
     * @param float $latitude The latitude of the google maps marker
     */
    public function setLatitude(float $latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Listener for the longitude event
     *
     * @param float $longitude The longitude of the google maps marker
     */
    public function setLongitude(float $longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Listener for the timezone event
     *
     * @param String $timezone The timezone of the google maps marker
     */
    public function setTimezone(String $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Listener for the elevation event
     *
     * @param float $elevation The elevation of the google maps marker
     */
    public function setElevation(float $elevation)
    {
        $this->elevation = $elevation;
    }

    /**
     * Listener for the country event
     *
     * @param String $country The country of the google maps marker
     */
    public function setCountry(String $country)
    {
        $this->country = $country;
    }

    public function mount()
    {
        if ($this->location->exists) {
            $this->update            = true;
            $this->name              = $this->location->name;
            $this->longitude         = $this->location->longitude;
            $this->latitude          = $this->location->latitude;
            $this->elevation         = $this->location->elevation;
            $this->timezone          = $this->location->timezone;
            $this->country           = $this->location->country;
            $this->limitingMagnitude = $this->location->limitingMagnitude;
            $this->bortle            = $this->location->bortle;
            $this->skyBackground     = $this->location->skyBackground;
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

        if ($propertyName == 'bortle') {
            $this->limitingMagnitude = MagnitudeController::bortleToNelm($this->bortle);
            $this->skyBackground     = MagnitudeController::bortleTosqm($this->bortle);
        }

        if ($propertyName == 'skyBackground') {
            $this->limitingMagnitude = round(MagnitudeController::sqmToNelm($this->skyBackground), 1);
            $this->bortle            = MagnitudeController::sqmToBortle($this->skyBackground);
        }

        if ($propertyName == 'limitingMagnitude') {
            $this->skyBackground     = round(MagnitudeController::nelmToSqm($this->limitingMagnitude), 2);
            $this->bortle            = MagnitudeController::nelmToBortle($this->limitingMagnitude);
        }
    }

    /**
     * Connects to lightpollutioninfo website
     * @return void
     */
    public function lightpollutioninfo()
    {
        $response = Http::get(
            'https://www.lightpollutionmap.info/QueryRaster/' .
             '?ql=wa_2015&qt=point&qd=' . $this->longitude
            . ',' . $this->latitude . '&key='
            . env('LIGHTPOLLUTION_KEY')
        );

        $lpNumber                = $response->body() + 0.132025599479675;
        $sqm                     = round(log10($lpNumber / 108000000) / -0.4, 2);
        $sqm > 22.0 ? $sqm       = 22.0 : $sqm;
        $this->skyBackground     = $sqm;
        $this->limitingMagnitude = round(MagnitudeController::sqmToNelm($this->skyBackground), 1);
        $this->bortle            = MagnitudeController::sqmToBortle($this->skyBackground);
    }

    public function save()
    {
        $this->validate();

        if ($this->update) {
            // Update the existing location
            $this->location->update(['name' => $this->name]);
            $this->location->update(['longitude' => $this->longitude]);
            $this->location->update(['latitude' => $this->latitude]);
            $this->location->update(['elevation' => $this->elevation]);
            $this->location->update(['timezone' => $this->timezone]);
            $this->location->update(['country' => $this->country]);
            $this->location->update(['limitingMagnitude' => $this->limitingMagnitude]);
            $this->location->update(['bortle' => $this->bortle]);
            $this->location->update(['skyBackground' => $this->skyBackground]);
        } else {
            // Create a new location
            $location = Location::create(
                ['user_id'                  => Auth::user()->id,
                    'name'                  => $this->name,
                    'longitude'             => $this->longitude,
                    'latitude'              => $this->latitude,
                    'elevation'             => $this->elevation,
                    'timezone'              => $this->timezone,
                    'country'               => $this->country,
                    'limitingMagnitude'     => $this->limitingMagnitude,
                    'bortle'                => $this->bortle,
                    'skyBackground'         => $this->skyBackground,
                    'active'                => 1, ]
            );
            laraflash(_i('Location %s created', $location->name))->success();
        }

        // Upload of the image
        if ($this->file) {
            // $this->validate([
            //     'photo' => 'image|max:10240',
            // ]);
            if (Location::find($location->id)->getFirstMedia('location') != null) {
                // First remove the current image
                Location::find($location->id)
                                ->getFirstMedia('location')
                                ->delete();
            }
            // Update the picture
            Location::find($location->id)
                        ->addFromMediaLibraryRequest($this->file)
                        ->toMediaCollection('location');
        }

        // View the page with all locations for the user
        return redirect(route('location.index'));
    }

    public function render()
    {
        return view('livewire.location.create');
    }
}
