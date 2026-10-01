<?php

namespace App\Livewire;

use App\Models\Location;
use App\Support\UploadedPhoto;
use deepskylog\AstronomyLibrary\Magnitude;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
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

    // Use Livewire validation rules (see $rules below). Removed an invalid PHP attribute
    // which could cause parse/syntax issues in some PHP versions/environments.
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
        // limit to 5MB by default (value is in kilobytes for Laravel validation)
        'photo' => 'nullable|image|max:5120',
    ];

    public function mount($location = null): void
    {
        if ($location) {
            abort_unless($location->canBeEditedBy(Auth::user()), 403);

            $this->location = $location;
            $this->update = true;
            $this->name = $location->name;
            $this->description = $location->description;
            $this->latitude = $location->latitude;
            $this->longitude = $location->longitude;
            $this->hidden = $location->hidden;
            $this->elevation = $location->elevation;
            $this->country = $location->country;
            $this->timezone = $location->timezone;

            if ($location->skyBackground > 0) {
                $this->sqm = $location->skyBackground ?? null;
                $this->updatedSqm($this->sqm);
            } elseif ($location->limitingMagnitude > 0) {
                // The stored NELM is without the fstOffset of the observer, the
                // form shows (and save() stores) it with the offset.
                $this->nelm = $location->getNelm(Auth()->user()->fstOffset ?? 0);
                $this->updatedNelm($this->nelm);
            }
            // Frontend initialization of the map is handled client-side via Livewire hooks
        }
    }

    public function save()
    {
        if ($this->location) {
            abort_unless($this->location->canBeEditedBy(Auth::user()), 403);
        }

        // Only look the elevation, country and time zone up for a new location,
        // moved coordinates or values that are still missing.
        if ($this->needsLookup()) {
            $this->updateElevation($this->latitude, $this->longitude);
            $this->updateCountry($this->latitude, $this->longitude);
        }

        $photoPath = null;

        $this->country = preg_replace('/\s*\(.*\)\s*/', '', $this->country);

        // Validate first to ensure uploaded file is acceptable before any processing.
        // The form values are validated, not the stored ones: -999 (no value) and
        // the NELM with the fstOffset are outside the ranges of the rules.
        $this->validate();

        // Make sure to add the nelm value without the fstOffset
        if ($this->sqm !== null && $this->sqm !== '') {
            $skyBackground = $this->sqm;
            $limitingMagnitude = ($this->nelm === null || $this->nelm === '')
                ? -999
                : $this->nelm + (Auth()->user()->fstOffset ?? 0);
        } else {
            $skyBackground = -999;
            $limitingMagnitude = -999;
        }

        // If a photo was uploaded, store it now.
        // dd($this->photo);
        if ($this->photo) {
            $photoPath = UploadedPhoto::store($this->photo, 'photos/locations', $this->location);

            $picture = $photoPath;
        }

        if ($this->update) {
            $this->location->update([
                'name' => $this->name,
                'description' => $this->description ?? '',
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'hidden' => $this->hidden,
                'elevation' => $this->elevation,
                'country' => $this->country,
                'timezone' => $this->timezone,
                'skyBackground' => $skyBackground,
                'limitingMagnitude' => $limitingMagnitude,
            ]);

            if ($this->photo) {
                $this->location->update([
                    'picture' => $picture,
                ]);
            }
            session()->flash('message', 'Location updated successfully!');

            // Return to /location/{user-slug}/{location-slug} page
            return redirect('/location/'.$this->location->user->slug.'/'.$this->location->slug);
        } else {
            $user_id = Auth::id();
            $observer = Auth::user()->username;

            $location = Location::create([
                'name' => $this->name,
                'description' => $this->description ?? '',
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'hidden' => $this->hidden,
                'elevation' => $this->elevation,
                'country' => $this->country,
                'timezone' => $this->timezone,
                'skyBackground' => $skyBackground,
                'limitingMagnitude' => $limitingMagnitude,
                'user_id' => $user_id,
                'observer' => $observer,
            ]);
            if ($this->photo) {
                $location->update([
                    'picture' => $picture,
                ]);
            }
            session()->flash('message', 'Location created successfully!');

            // Return to /location/{user-slug}/{location-slug} page
            return redirect('/location/'.Auth()->user()->slug.'/'.$location->slug);
        }
    }

    public function setDescription($value): void
    {
        $this->description = $value;
    }

    private function needsLookup(): bool
    {
        if (! $this->update) {
            return true;
        }

        $moved = abs((float) $this->latitude - (float) $this->location->latitude) > 1e-6
            || abs((float) $this->longitude - (float) $this->location->longitude) > 1e-6;

        return $moved
            || $this->elevation === null || $this->elevation === ''
            || empty($this->country) || $this->country === 'Unknown'
            || empty($this->timezone);
    }

    /**
     * Looks the elevation up. On a failure the current value is kept (0 when there is none).
     */
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
            $this->elevation = $data['results'][0]['elevation'] ?? $this->elevation ?? 0;
        } catch (GuzzleException) {
            $this->elevation ??= 0;
        }
    }

    /**
     * Looks the country and time zone up. On a failure the current values are
     * kept ('Unknown' and 'UTC' when there are none).
     */
    public function updateCountry($latitude, $longitude): void
    {
        $client = new Client;
        try {
            $response = $client->get('https://api.bigdatacloud.net/data/reverse-geocode-client', [
                'query' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'localityLanguage' => 'en',
                    'key' => config('services.bigdatacloud.key'),
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            $this->country = ($data['countryName'] ?? '') ?: ($this->country ?: 'Unknown');
        } catch (GuzzleException $e) {
            $this->country = $this->country ?: 'Unknown';
        }

        $client2 = new Client;
        try {
            $response = $client2->get('https://api-bdc.net/data/timezone-by-location', [
                'query' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'key' => config('services.bigdatacloud.key'),
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            $this->timezone = ($data['ianaTimeId'] ?? '') ?: ($this->timezone ?: 'UTC');
        } catch (GuzzleException $e) {
            $this->timezone = $this->timezone ?: 'UTC';
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
                    'key' => config('services.lightpollutionmap.key'),
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
