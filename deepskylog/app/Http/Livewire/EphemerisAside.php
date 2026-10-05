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
use deepskylog\AstronomyLibrary\Targets\AstroTarget;
use deepskylog\AstronomyLibrary\Time;
use Illuminate\Support\Facades\DB;

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
        'setEphemerisDate' => 'setDateFromEvent',
        'dsl-timezone-detected' => 'timezoneDetected',
    ];

    public function mount()
    {
        // Keep the date chosen earlier in this session, so every page, object
        // set and report uses the same date; else today for the observer (in
        // the browser's timezone for guests).
        $this->date = EphemerisDate::current();
        $this->recalculate();
        // Nothing is stored here: storing today's date made it stick for the
        // whole session. Server-rendered components get the same date from
        // EphemerisDate::current().
        // Broadcast initial date so other Livewire components (tables, previews)
        // receive the same ephemeris date on first render.
        try {
            $this->dispatch('ephemerisDateChanged', date: $this->date);
        } catch (\Throwable $_) {
            // ignore
        }
    }

    public function updatedDate($value)
    {
        $this->dateChanged();
    }

    public function setDateFromEvent($d)
    {
        $this->date = $d ?: EphemerisDate::today();
        $this->dateChanged();
    }

    /**
     * The layout's script has just stored the browser's timezone in a cookie
     * (first visit, or a new timezone): without a chosen date, move to today
     * in that timezone.
     */
    public function timezoneDetected(): void
    {
        if (EphemerisDate::chosen() !== null) {
            return;
        }

        $today = EphemerisDate::today();
        if ($today === $this->date) {
            return;
        }

        $this->date = $today;
        $this->recalculate();
        try {
            $this->dispatch('ephemerisDateChanged', date: $this->date);
        } catch (\Throwable $_) {
        }
    }

    /**
     * Recalculate the aside for the new date, keep it in the session and
     * broadcast it. Every component on the page (object ephemerides, moon/sun/
     * comet details, Aladin preview, tables) recalculates itself on
     * `ephemerisDateChanged`; nothing else is sent to them, so a date change
     * recalculates each of them once.
     */
    protected function dateChanged(): void
    {
        $this->recalculate();
        EphemerisDate::choose($this->date);
        try {
            $this->dispatch('ephemerisDateChanged', date: $this->date);
        } catch (\Throwable $_) {
            // ignore
        }
        // A lightweight browser event with the freshly computed aside values.
        // It leaves out the Moon's illuminated_fraction, so generic client-side
        // listeners cannot overwrite the illumination of a planet with it.
        try {
            $this->dispatch('dsl-ephemeris-aside-updated', ...[
                'date' => $this->date,
                'rising' => $this->moon_rise ?? null,
                'setting' => $this->moon_set ?? null,
                'moon_illumination' => $this->moon_illuminated ?? null,
                'next_new_moon' => $this->next_new_moon ?? null,
                '_ts' => Carbon::now()->toIso8601String(),
            ]);
        } catch (\Throwable $_) {
        }
    }

    protected function recalculate()
    {
        try {
            $loc = $this->location();

            // default placeholders if no location available
            if (!$loc instanceof Location) {
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
            $timezone = EphemerisDate::timezone($loc);
            $d = EphemerisDate::parse($this->date, $timezone)->setTime(12, 0);

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

                    // Calculate equatorial coords for the Moon using the local
                    // astronomy library.
                    try {
                        $moonTarget->calculateEquatorialCoordinates($d->copy(), $geo_coords, $loc->elevation ?? 0.0);
                        // calculate rise/transit/set
                        $moonTarget->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);
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

    /**
     * The location of location(), looked up once per request.
     */
    protected ?Location $resolvedLocation = null;

    protected bool $locationResolved = false;

    /**
     * The user's standard location, else the default location for guests.
     */
    protected function location(): ?Location
    {
        if (! $this->locationResolved) {
            try {
                $this->resolvedLocation = Location::forEphemerides(Auth::user());
            } catch (\Throwable $_) {
                $this->resolvedLocation = null;
            }
            $this->locationResolved = true;
        }

        return $this->resolvedLocation;
    }

    public function render()
    {
        // Always render the global ephemeris aside. Previously deep-sky
        // object pages suppressed this Livewire component which hid the
        // left aside on pages like M31; we now render it so the ephemeris
        // aside is visible for deep-sky object pages as well.
        return view('livewire.ephemeris-aside');
    }
}
