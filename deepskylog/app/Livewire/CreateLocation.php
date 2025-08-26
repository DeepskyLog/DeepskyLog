<?php

namespace App\Livewire;

use App\Models\Location;
use deepskylog\AstronomyLibrary\Magnitude;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateLocation extends Component
{
    use WithFileUploads;

    public $location;

    public $update = false;

    public $name;

    public $description;

    public $latitude;

    public $longitude;

    public $elevation;

    public $hidden = false;

    public $country;

    public $timezone;

    public $sqm;

    public $nelm;

    public $bortle;

    #[Validate('image')]
    public $photo;

    protected $listeners = [
        'setDescription',
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'hidden' => 'boolean',
        'elevation' => 'nullable|numeric',
        'sqm' => 'nullable|numeric|min:15|max:22',
        'nelm' => 'nullable|numeric|min:0|max:8',
        'bortle' => 'nullable|integer|between:1,9',
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
            $this->sqm = $location->sqm ?? null;
            $this->nelm = $location->nelm ?? null;
            $this->bortle = $location->bortle ?? null;
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
                'sqm' => $this->sqm,
                'nelm' => $this->nelm,
                'bortle' => $this->bortle,
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
                'sqm' => $this->sqm,
                'nelm' => $this->nelm,
                'bortle' => $this->bortle,
            ]);
            session()->flash('message', 'Location created successfully!');
            $this->reset(['name', 'description', 'latitude', 'longitude', 'hidden', 'sqm', 'nelm', 'bortle']);
        }
    }

    public function setDescription($value): void
    {
        $this->description = $value;
    }

    public function updateElevation($latitude, $longitude): void
    {
        // TODO: Only update the elevation, country and timezone if the location is saved.
        // TODO: Add Extra fields
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

            $this->updateCountry($latitude, $longitude);
        } catch (GuzzleException) {
            $this->elevation = 0;
        }
    }

    public function updateCountry($latitude, $longitude): void
    {
        $client = new Client;
        try {
            $response = $client->get('https://api.bigdatacloud.net/data/reverse-geocode-client', [
                'query' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'localityLanguage' => 'en',
                    'key' => env('BIGDATA_API_KEY'),
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            $this->country = $data['countryName'] ?? 'Unknown';
        } catch (GuzzleException $e) {
            $this->country = 'Unknown';
        }

        $client2 = new Client;
        try {
            $response = $client2->get('https://api-bdc.net/data/timezone-by-location', [
                'query' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'key' => env('BIGDATA_API_KEY'),
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            $this->timezone = $data['ianaTimeId'] ?? 'UTC';
        } catch (GuzzleException $e) {
            $this->timezone = 'UTC';
        }
    }

    public function render(): \Illuminate\Contracts\View\View|Application|Factory|View
    {
        return view('livewire.create-location');
    }

    /**
     * When the SQM value is updated in the UI, calculate and set NELM and Bortle.
     */
    public function updatedSqm($value): void
    {
        if ($value === null || $value === '') {
            $this->nelm = null;
            $this->bortle = null;

            return;
        }

        // Ensure numeric
        if (! is_numeric($value)) {
            $this->nelm = null;
            $this->bortle = null;

            return;
        }

        try {
            $fstOffset = auth()->check() ? (auth()->user()->fstOffset ?? 0) : 0;
            // NELM: one decimal like Location::getNelm
            $this->nelm = round(Magnitude::sqmToNelm((float) $value, $fstOffset), 1);
            // Bortle: integer (Magnitude methods return integer)
            $this->bortle = Magnitude::sqmToBortle((float) $value);
        } catch (\Throwable $e) {
            // In case of any error, clear the derived values
            $this->nelm = null;
            $this->bortle = null;
        }
    }

    /**
     * When the NELM value is updated in the UI, calculate and set SQM and Bortle.
     * Includes a small tolerance guard to avoid back-and-forth updates between fields.
     */
    public function updatedNelm($value): void
    {
        if ($value === null || $value === '') {
            $this->sqm = null;
            $this->bortle = null;

            return;
        }

        if (! is_numeric($value)) {
            $this->sqm = null;
            $this->bortle = null;

            return;
        }

        try {
            $fstOffset = auth()->check() ? (auth()->user()->fstOffset ?? 0) : 0;
            // SQM: round to 2 decimals as in Location::getSqm when converted from NELM
            $computedSqm = round(Magnitude::nelmToSqm((float) $value, $fstOffset), 2);
            $computedBortle = Magnitude::nelmToBortle((float) $value);

            // Avoid tiny oscillations: only update if difference is noticeable
            if ($this->sqm === null || abs((float) $this->sqm - $computedSqm) > 0.01) {
                $this->sqm = $computedSqm;
            }

            if ($this->bortle === null || (int) $this->bortle !== (int) $computedBortle) {
                $this->bortle = $computedBortle;
            }
        } catch (\Throwable $e) {
            $this->sqm = null;
            $this->bortle = null;
        }
    }

    /**
     * When the Bortle value is updated in the UI, calculate and set SQM and NELM.
     */
    public function updatedBortle($value): void
    {
        if ($value === null || $value === '') {
            $this->sqm = null;
            $this->nelm = null;

            return;
        }

        // Accept numeric or integer-like values
        if (! is_numeric($value)) {
            $this->sqm = null;
            $this->nelm = null;

            return;
        }

        try {
            $b = (int) $value;

            // Assumed helper methods exist to convert from Bortle to NELM and SQM.
            $fstOffset = auth()->check() ? (auth()->user()->fstOffset ?? 0) : 0;
            $computedNelm = Magnitude::bortleToNelm($b, $fstOffset);
            $computedSqm = Magnitude::bortleToSqm($b);

            // Round to match other methods: NELM one decimal, SQM two decimals
            if ($computedNelm !== null) {
                $computedNelm = round($computedNelm, 1);
            }
            if ($computedSqm !== null) {
                $computedSqm = round($computedSqm, 2);
            }

            // Update if noticeably different to avoid small oscillations
            if ($this->nelm === null || abs((float) $this->nelm - (float) $computedNelm) > 0.01) {
                $this->nelm = $computedNelm;
            }

            if ($this->sqm === null || abs((float) $this->sqm - (float) $computedSqm) > 0.01) {
                $this->sqm = $computedSqm;
            }
        } catch (\Throwable $e) {
            $this->sqm = null;
            $this->nelm = null;
        }
    }

    public function fetchLightPollutionData(): void
    {
        if (! $this->latitude || ! $this->longitude) {
            session()->flash('message', 'Please provide valid latitude and longitude values.');

            return;
        }

        $client = new Client;
        try {
            $response = $client->get('https://www.lightpollutionmap.info/QueryRaster/', [
                'query' => [
                    'ql' => 'wa_2015',
                    'qt' => 'point',
                    'qd' => $this->longitude.','.$this->latitude,
                    'key' => env('LIGHTPOLLUTIONMAP_API_KEY'),
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if ($data != null) {
                // Convert the value to a number and add the natural sky brightness
                $lpNumber = (float) $data + 0.132025599479675;

                // Calculate SQM
                $sqm = log10($lpNumber / 108000000) / -0.4;

                // Update the SQM field and trigger updates for NELM and Bortle
                $this->sqm = round($sqm, 2);
                $this->updatedSqm($this->sqm);
            } else {
                session()->flash('message', 'Error fetching data from Light Pollution Map.');
            }
        } catch (GuzzleException $e) {
            session()->flash('message', 'Error fetching data from Light Pollution Map.');
        }
    }
}
