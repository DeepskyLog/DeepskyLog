<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Location;
use App\Support\EphemerisDate;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Moon as AstroMoon;
use deepskylog\AstronomyLibrary\Time;

class MoonDetails extends Component
{
    public $objectId;
    public $initial;

    public $rising = null;
    public $transit = null;
    public $setting = null;
    public $illuminated_fraction = null;
    public $next_new_moon = null;

    protected $listeners = [
        'objectEphemeridesUpdated' => 'handleUpdated',
        'ephemerisDateChanged' => 'handleDateChanged',
        'ephemerisPayloadUpdated' => 'handlePayload',
    ];

    public function mount($objectId = null, $initial = null)
    {
        $this->objectId = $objectId;
        $this->initial = $initial;
        // Initialize from provided initial ephemerides payload if available
        try {
            $p = $initial;
            if (is_array($p)) {
                $this->rising = $p['rising'] ?? ($p['ephemerides']['rising'] ?? null) ?? $this->rising;
                $this->transit = $p['transit'] ?? ($p['ephemerides']['transit'] ?? null) ?? $this->transit;
                $this->setting = $p['setting'] ?? ($p['ephemerides']['setting'] ?? null) ?? $this->setting;
                $this->illuminated_fraction = $p['illuminated_fraction'] ?? ($p['ephemerides']['illuminated_fraction'] ?? null) ?? $this->illuminated_fraction;
                // Backwards-compatible: accept new moon_illumination keys as well
                if (empty($this->illuminated_fraction)) {
                    $this->illuminated_fraction = $p['moon_illumination'] ?? ($p['ephemerides']['moon_illumination'] ?? ($p['moonIllumination'] ?? null)) ?? $this->illuminated_fraction;
                }
                $this->next_new_moon = $p['next_new_moon'] ?? ($p['ephemerides']['next_new_moon'] ?? null) ?? $this->next_new_moon;
            }
        } catch (\Throwable $_) {
            // ignore
        }
        // If we have no initial data, compute values for today.
        if (empty($this->rising) && empty($this->setting) && empty($this->illuminated_fraction)) {
            try {
                $this->recalculateForDate($initial['date'] ?? Carbon::now()->toDateString());
            } catch (\Throwable $_) {
                // ignore
            }
        }
    }

    public function handleUpdated($payload = null)
    {
        try {
            $p = $payload ?? [];
            if (isset($p['payload']) && is_array($p['payload'])) $p = $p['payload'];
            if (isset($p['ephemerides']) && is_array($p['ephemerides'])) $p = array_merge($p, $p['ephemerides']);

            $this->rising = $p['rising'] ?? ($p['rise'] ?? $this->rising);
            $this->transit = $p['transit'] ?? $this->transit;
            $this->setting = $p['setting'] ?? ($p['set'] ?? $this->setting);
            $this->illuminated_fraction = $p['illuminated_fraction'] ?? ($p['illuminatedFraction'] ?? $this->illuminated_fraction);
            if (empty($this->illuminated_fraction)) {
                $this->illuminated_fraction = $p['moon_illumination'] ?? ($p['moonIllumination'] ?? ($p['ephemerides']['moon_illumination'] ?? null)) ?? $this->illuminated_fraction;
            }
            $this->next_new_moon = $p['next_new_moon'] ?? ($p['nextNewMoon'] ?? $this->next_new_moon);
            // Values updated; Livewire will re-render the view.
        } catch (\Throwable $e) {
            // swallow errors to avoid breaking UI
        }
    }

    public function handlePayload($payload = null)
    {
        try {
            $p = $payload ?? [];
            if (isset($p['payload']) && is_array($p['payload'])) $p = $p['payload'];
            if (isset($p['ephemerides']) && is_array($p['ephemerides'])) $p = array_merge($p, $p['ephemerides']);

            $this->rising = $p['rising'] ?? ($p['rise'] ?? $this->rising);
            $this->transit = $p['transit'] ?? $this->transit;
            $this->setting = $p['setting'] ?? ($p['set'] ?? $this->setting);
            $this->illuminated_fraction = $p['illuminated_fraction'] ?? ($p['illuminatedFraction'] ?? $this->illuminated_fraction);
            if (empty($this->illuminated_fraction)) {
                $this->illuminated_fraction = $p['moon_illumination'] ?? ($p['moonIllumination'] ?? ($p['ephemerides']['moon_illumination'] ?? null)) ?? $this->illuminated_fraction;
            }
            $this->next_new_moon = $p['next_new_moon'] ?? ($p['nextNewMoon'] ?? $this->next_new_moon);
        } catch (\Throwable $_) {
            // ignore
        }
    }

    public function handleDateChanged($date = null)
    {
        try {
            $d = $date ?: Carbon::now()->toDateString();
            $this->recalculateForDate($d);
        } catch (\Throwable $_) {
            // ignore
        }
    }

    protected function recalculateForDate($date)
    {
        try {
            // Determine location (user standardLocation or fallback)
            $user = Auth::user();
            $loc = null;
            if ($user && $user->standardLocation) {
                $loc = $user->standardLocation;
            } else {
                try {
                    $loc = Location::forEphemerides();
                } catch (\Throwable $_) {
                    $loc = null;
                }
            }

            if (! $loc instanceof Location) {
                return;
            }

            // Noon of the date at the location, as in the ephemeris aside, so
            // both show the same rise, set and illumination
            $timezone = EphemerisDate::timezone($loc);
            $d = EphemerisDate::parse($date, $timezone)->setTime(12, 0);

            $coords = new GeographicalCoordinates($loc->longitude, $loc->latitude);
            $moonTarget = new AstroMoon();

            try {
                $moonTarget->illuminatedFraction($d);
            } catch (\Throwable $_) {
            }

            try {
                $this->illuminated_fraction = $moonTarget->illuminatedFraction($d) ?? null;
            } catch (\Throwable $_) {
                $this->illuminated_fraction = null;
            }

            try {
                $next = $moonTarget->newMoonDate($d);
                $this->next_new_moon = $next ? Carbon::instance($next)->timezone($timezone)->toDateString() : null;
            } catch (\Throwable $_) {
                $this->next_new_moon = null;
            }

            // Compute rise/set
            try {
                $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($d);
                $deltaT = Time::deltaT($d);

                try {
                    $moonTarget->calculateEquatorialCoordinates($d->copy(), $coords, $loc->elevation ?? 0.0);
                    $moonTarget->calculateEphemerides($coords, $greenwichSiderialTime, $deltaT);
                } catch (\Throwable $_) {
                    // ignore: no rise and set times
                }

                $moonRise = null;
                $moonSet = null;
                try {
                    $moonRise = $moonTarget->getRising();
                } catch (\Throwable $_) {
                    $moonRise = null;
                }
                try {
                    $moonSet = $moonTarget->getSetting();
                } catch (\Throwable $_) {
                    $moonSet = null;
                }

                $this->rising = $moonRise instanceof \DateTimeInterface ? Carbon::instance($moonRise)->timezone($timezone)->isoFormat('HH:mm') : '-';
                $this->setting = $moonSet instanceof \DateTimeInterface ? Carbon::instance($moonSet)->timezone($timezone)->isoFormat('HH:mm') : '-';
            } catch (\Throwable $_) {
                $this->rising = '-';
                $this->setting = '-';
            }
        } catch (\Throwable $_) {
            // ignore
        }
    }



    public function render()
    {
        return view('livewire.moon-details');
    }
}
