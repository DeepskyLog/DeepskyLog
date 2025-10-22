<?php
namespace App\Http\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Location;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Moon as AstroMoon;
use deepskylog\AstronomyLibrary\Time;

class EphemerisAside extends Component
{
    public $date;
    // computed properties for the view
    public $sun_times = null;
    public $nautical = null;
    public $astronomical = null;
    public $moon_phase_ratio = null;
    public $moon_illuminated = null;
    public $next_new_moon = null;
    public $moon_rise = null;
    public $moon_set = null;

    protected $listeners = [
        'setEphemerisDate' => 'setDateFromEvent'
    ];

    public function mount()
    {
        $this->date = Carbon::now()->toDateString();
        $this->recalculate();
    }

    public function updatedDate($value)
    {
        // Broadcast an event so other Livewire components can update (Livewire v3 uses dispatch())
        try {
            // Target commonly-listening components so server-side listeners receive the date change
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date)->to('object-ephemerides');
            } catch (\Throwable $_) {
                // ignore target failure
            }
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date)->to('aladin-preview-info');
            } catch (\Throwable $_) {
                // ignore target failure
            }
            // also emit a generic dispatch for other listeners
            $this->dispatch('ephemerisDateChanged', date: $this->date);
        } catch (\Throwable $_) {
            // fallback for older Livewire versions (noop)
        }
        $this->recalculate();
    }

    public function setDateFromEvent($d)
    {
        $this->date = $d ?: Carbon::now()->toDateString();
        try {
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date)->to('object-ephemerides');
            } catch (\Throwable $_) {}
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date)->to('aladin-preview-info');
            } catch (\Throwable $_) {}
            $this->dispatch('ephemerisDateChanged', date: $this->date);
        } catch (\Throwable $_) {
            // fallback
        }
        $this->recalculate();
    }

    protected function recalculate()
    {
        try {
            $user = Auth::user();
            $loc = null;
            if ($user && $user->standardLocation) {
                $loc = $user->standardLocation;
            } else {
                // fallback: first active Location row in DB (no user-specific fallback available)
                try {
                    $loc = Location::where('active', 1)->first();
                } catch (\Throwable $_) {
                    $loc = null;
                }
            }

            // default placeholders if no location available
            if (! $loc instanceof Location) {
                $this->sun_times = '- / - / -';
                $this->nautical = '- / -';
                $this->astronomical = '- / -';
                $this->moon_phase_ratio = null;
                $this->moon_illuminated = null;
                $this->next_new_moon = null;
                $this->moon_rise = '-';
                $this->moon_set = '-';
                return;
            }

            // Use Location helper methods which respect Session date; set local date for calculations
            // We'll create a Carbon at midday in the location timezone to avoid DST issues
            try {
                $d = Carbon::createFromFormat('Y-m-d', $this->date);
            } catch (\Exception $_) {
                $d = Carbon::now();
            }
            $d->hour = 12;

            $timezone = $loc->timezone ?? config('app.timezone');

            // Sunrise / Sunset / Transit
            // The Location methods read Session::date; for safety compute directly here using date_sun_info
            $sun_info = date_sun_info($d->timestamp, $loc->latitude, $loc->longitude);

            // Format with timezone
            $tz = $timezone;
            $this->sun_times = (
                isset($sun_info['sunrise']) && is_int($sun_info['sunrise']) ? Carbon::createFromTimestamp($sun_info['sunrise'])->timezone($tz)->isoFormat('HH:mm') : '-' 
            ) . ' / ' . (
                isset($sun_info['sunset']) && is_int($sun_info['sunset']) ? Carbon::createFromTimestamp($sun_info['sunset'])->timezone($tz)->isoFormat('HH:mm') : '-'
            ) . ' / ' . (
                isset($sun_info['transit']) && is_int($sun_info['transit']) ? Carbon::createFromTimestamp($sun_info['transit'])->timezone($tz)->isoFormat('HH:mm') : '-'
            );

            // Nautical
            $this->nautical = (
                isset($sun_info['nautical_twilight_begin']) && is_int($sun_info['nautical_twilight_begin']) ? Carbon::createFromTimestamp($sun_info['nautical_twilight_begin'])->timezone($tz)->isoFormat('HH:mm') : '-'
            ) . ' / ' . (
                isset($sun_info['nautical_twilight_end']) && is_int($sun_info['nautical_twilight_end']) ? Carbon::createFromTimestamp($sun_info['nautical_twilight_end'])->timezone($tz)->isoFormat('HH:mm') : '-'
            );

            // Astronomical
            $this->astronomical = (
                isset($sun_info['astronomical_twilight_begin']) && is_int($sun_info['astronomical_twilight_begin']) ? Carbon::createFromTimestamp($sun_info['astronomical_twilight_begin'])->timezone($tz)->isoFormat('HH:mm') : '-'
            ) . ' / ' . (
                isset($sun_info['astronomical_twilight_end']) && is_int($sun_info['astronomical_twilight_end']) ? Carbon::createFromTimestamp($sun_info['astronomical_twilight_end'])->timezone($tz)->isoFormat('HH:mm') : '-'
            );

            // Use AstronomyLibrary + Target Moon for moon details: phase ratio, illuminated fraction, next new moon and rise/set
            try {
                $coords = new GeographicalCoordinates($loc->longitude, $loc->latitude);
                $astrolib = new AstronomyLibrary($d, $coords, $loc->elevation ?? 0.0);

                // Phase and illuminated fraction via Target Moon helpers
                $moonTarget = new AstroMoon();
                // illuminatedFraction and getPhaseRatio expect a Carbon date
                try {
                    $this->moon_illuminated = $moonTarget->illuminatedFraction($d) ?? null;
                } catch (\Throwable $_) {
                    $this->moon_illuminated = null;
                }
                try {
                    $this->moon_phase_ratio = $moonTarget->getPhaseRatio($d) ?? null;
                } catch (\Throwable $_) {
                    $this->moon_phase_ratio = null;
                }

                // Next new moon
                try {
                    $next = $moonTarget->newMoonDate($d);
                    $this->next_new_moon = $next ? Carbon::instance($next)->timezone($tz)->toDateString() : null;
                } catch (\Throwable $_) {
                    $this->next_new_moon = null;
                }

                // Moon rise / set: calculate equatorial coords and ephemerides for the Moon
                try {
                    // prepare geographic coords and time primitives as ObjectController does
                    $geo_coords = $coords;
                    $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($d);
                    $deltaT = Time::deltaT($d);

                    // calculate equatorial coords for moon (today/yesterday/tomorrow)
                    $moonTarget->calculateEquatorialCoordinates($d->copy(), $geo_coords, $loc->elevation ?? 0.0);
                    // calculate rise/transit/set
                    $moonTarget->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);

                    $moonRise = null; $moonSet = null;
                    try { $moonRise = $moonTarget->getRising(); } catch (\Throwable $_) { $moonRise = null; }
                    try { $moonSet = $moonTarget->getSetting(); } catch (\Throwable $_) { $moonSet = null; }

                    $this->moon_rise = $moonRise instanceof \DateTimeInterface ? Carbon::instance($moonRise)->timezone($tz)->isoFormat('HH:mm') : '-';
                    $this->moon_set = $moonSet instanceof \DateTimeInterface ? Carbon::instance($moonSet)->timezone($tz)->isoFormat('HH:mm') : '-';
                } catch (\Throwable $_) {
                    $this->moon_rise = '-';
                    $this->moon_set = '-';
                }
            } catch (\Throwable $_) {
                // leave moon values null or dash
                $this->moon_phase_ratio = null;
                $this->moon_illuminated = null;
                $this->next_new_moon = null;
                $this->moon_rise = '-';
                $this->moon_set = '-';
            }
        } catch (\Throwable $e) {
            // fallback: keep placeholders
        }
    }

    public function render()
    {
        // Placeholder: real ephemeris calculations should be done in a service
        return view('livewire.ephemeris-aside');
    }
}
