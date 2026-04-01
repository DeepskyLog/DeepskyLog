<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Location;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Moon as AstroMoon;
use deepskylog\AstronomyLibrary\Targets\AstroTarget;
use deepskylog\AstronomyLibrary\Time;
use App\Http\Livewire\ObjectEphemerides as LWObjectEphemerides;
use Illuminate\Support\Facades\Log;
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
        'setEphemerisDate' => 'setDateFromEvent'
    ];

    public function mount()
    {
        $this->date = Carbon::now()->toDateString();
        $this->recalculate();
        // Persist canonical aside date in session so other server-rendered
        // components can read it during initial render and compute matching
        // ephemerides without waiting for Livewire hydration events.
        try {
            session()->put('dsl_ephemeris_date', $this->date);
        } catch (\Throwable $_) {
            // ignore session failures
        }
        // Broadcast initial date so other Livewire components (tables, previews)
        // receive the same ephemeris date on first render.
        try {
            $this->emit('ephemerisDateChanged', $this->date);
        } catch (\Throwable $_) {
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date);
            } catch (\Throwable $_) {
                // ignore
            }
        }
    }

    public function updatedDate($value)
    {
        // Broadcast an event so other Livewire components can update (Livewire v3 uses dispatch())
        try {
            try {
                \Illuminate\Support\Facades\Log::debug('EphemerisAside: updatedDate dispatching', ['date' => $this->date]);
            } catch (\Throwable $_) {
            }
            // Target commonly-listening components so server-side listeners receive the date change
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date)->to('object-ephemerides');
            } catch (\Throwable $_) {
                // ignore target failure
            }
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date)->to('moon-details');
            } catch (\Throwable $_) {
                // ignore
            }
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date)->to('aladin-preview-info');
            } catch (\Throwable $_) {
                // ignore target failure
            }
            // also emit a generic event for other listeners (date-only)
            try {
                $this->emit('ephemerisDateChanged', $this->date);
            } catch (\Throwable $_) {
                // fallback to dispatch when emit is unavailable
                $this->dispatch('ephemerisDateChanged', date: $this->date);
            }
            try {
                \Illuminate\Support\Facades\Log::debug('EphemerisAside: emitTo object-ephemerides ephemerisDateChanged', ['date' => $this->date]);
            } catch (\Throwable $_) {
            }
            try {
                $this->emitTo('object-ephemerides', 'ephemerisDateChanged', $this->date);
            } catch (\Throwable $_) {
            }
        } catch (\Throwable $_) {
            // fallback for older Livewire versions (noop)
        }
        $this->recalculate();
        // Keep session in sync with updated aside date
        try {
            session()->put('dsl_ephemeris_date', $this->date);
        } catch (\Throwable $_) {
            // ignore
        }
        // Dispatch a lightweight browser event with the freshly computed aside payload
        try {
            $payload = [
                'date' => $this->date,
                'rising' => $this->moon_rise ?? null,
                'setting' => $this->moon_set ?? null,
                'illuminated_fraction' => $this->moon_illuminated ?? null,
                'moon_illumination' => $this->moon_illuminated ?? null,
                'next_new_moon' => $this->next_new_moon ?? null,
                '_ts' => \Carbon\Carbon::now()->toIso8601String(),
            ];
            // Build a sanitized payload for object-focused listeners that must not
            // receive the Moon's illuminated fraction (prevents moon illumination
            // overwriting planet illumination on object pages).
            $payloadForObjects = $payload;
            if (array_key_exists('illuminated_fraction', $payloadForObjects))
                unset($payloadForObjects['illuminated_fraction']);

            try {
                // Dispatch a sanitized browser event to avoid exposing the Moon's
                // illuminated_fraction to generic client-side listeners which
                // may update planet pages incorrectly. Moon-specific Livewire
                // listeners still receive the full payload via emitTo below.
                $browserPayload = $payloadForObjects;
                $this->dispatchBrowserEvent('dsl-ephemeris-aside-updated', $browserPayload);
            } catch (\Throwable $_) {
            }
            // Emit a sanitized payload to general Livewire listeners so object
            // ephemerides components do not receive the Moon's illumination.
            try {
                $this->emit('ephemerisPayloadUpdated', $payloadForObjects);
            } catch (\Throwable $_) {
                try {
                    $this->dispatch('ephemerisPayloadUpdated', payload: $payloadForObjects)->to('object-ephemerides');
                } catch (\Throwable $__) {
                    $this->dispatch('ephemerisPayloadUpdated', payload: $payloadForObjects);
                }
            }

            // Also attempt a server-side authoritative recompute for the
            // current page object and emit the resulting ephemerides so
            // components that missed Livewire dispatching still get updates.
            try {
                try {
                    Log::debug('EphemerisAside: attempting server-side recompute for current page object', ['date' => $this->date]);
                } catch (\Throwable $_) {
                }
                $useObjectId = null;
                try {
                    $req = request();
                    if ($req && method_exists($req, 'route')) {
                        $useObjectId = $req->route('id') ?? $req->route('object') ?? $useObjectId;
                    }
                    if (empty($useObjectId) && $req) {
                        $useObjectId = $req->query('id') ?? $req->query('object') ?? $useObjectId;
                    }
                } catch (\Throwable $_) {
                    $useObjectId = null;
                }
                if (!empty($useObjectId)) {
                    $lw = new LWObjectEphemerides();
                    $lw->objectId = $useObjectId;
                    $lw->suppressEphemerides = false;
                    try {
                        $lw->recalculate(['date' => $this->date, 'objectId' => $useObjectId, 'sourceTypeRaw' => null]);
                    } catch (\Throwable $_) {
                    }
                    if (!empty($lw->ephemerides) && is_array($lw->ephemerides)) {
                        $preview = ['objectId' => $useObjectId, 'ephemerides' => $lw->ephemerides];
                        try {
                            Log::debug('EphemerisAside: emitting objectEphemeridesUpdated from aside recompute', ['objectId' => $useObjectId, 'ephemerides' => $lw->ephemerides]);
                        } catch (\Throwable $_) {
                        }
                        try {
                            // Sanitize preview: avoid including a Moon illuminated_fraction
                            // when the preview targets a non-Moon object. This prevents
                            // the aside recompute from overwriting planet pages with
                            // Moon illumination.
                            $sanitizedPreview = $preview;
                            $targetId = is_string($useObjectId) ? mb_strtolower(trim((string) $useObjectId)) : null;
                            // Remove moon illumination fields for non-moon targets
                            if ($targetId !== 'moon') {
                                if (isset($sanitizedPreview['ephemerides']) && is_array($sanitizedPreview['ephemerides'])) {
                                    if (array_key_exists('illuminated_fraction', $sanitizedPreview['ephemerides'])) {
                                        unset($sanitizedPreview['ephemerides']['illuminated_fraction']);
                                    }
                                    if (array_key_exists('moon_illumination', $sanitizedPreview['ephemerides'])) {
                                        unset($sanitizedPreview['ephemerides']['moon_illumination']);
                                    }
                                }
                                if (array_key_exists('illuminated_fraction', $sanitizedPreview))
                                    unset($sanitizedPreview['illuminated_fraction']);
                                if (array_key_exists('moon_illumination', $sanitizedPreview))
                                    unset($sanitizedPreview['moon_illumination']);
                            } else {
                                // For moon target, ensure we expose the moon_illumination key
                                if (isset($sanitizedPreview['ephemerides']) && is_array($sanitizedPreview['ephemerides'])) {
                                    $sanitizedPreview['ephemerides']['moon_illumination'] = $sanitizedPreview['ephemerides']['illuminated_fraction'] ?? ($sanitizedPreview['ephemerides']['moon_illumination'] ?? null);
                                }
                                $sanitizedPreview['moon_illumination'] = $sanitizedPreview['ephemerides']['moon_illumination'] ?? ($sanitizedPreview['moon_illumination'] ?? null);
                            }
                            $this->emit('objectEphemeridesUpdated', $sanitizedPreview);
                        } catch (\Throwable $_) {
                            try {
                                $this->dispatch('objectEphemeridesUpdated', payload: $sanitizedPreview)->to('object-ephemerides');
                            } catch (\Throwable $_) {
                            }
                        }
                    }
                }
            } catch (\Throwable $_) {
            }

            try {
                \Illuminate\Support\Facades\Log::debug('EphemerisAside: emitTo object-ephemerides ephemerisPayloadUpdated', ['payload' => $payloadForObjects]);
            } catch (\Throwable $_) {
            }
            try {
                $this->emitTo('object-ephemerides', 'ephemerisPayloadUpdated', $payloadForObjects);
            } catch (\Throwable $_) {
            }

            // Also send a moon-only payload directly to moon-details so it uses
            // the exact same computed values as the aside.
            try {
                try {
                    $this->emitTo('moon-details', 'ephemerisPayloadUpdated', $payload);
                } catch (\Throwable $_) {
                    try {
                        $this->dispatch('ephemerisPayloadUpdated', payload: $payload)->to('moon-details');
                    } catch (\Throwable $__) {
                        $this->dispatch('ephemerisPayloadUpdated', payload: $payload);
                    }
                }
            } catch (\Throwable $_) {
            }
        } catch (\Throwable $_) {
        }
    }

    public function setDateFromEvent($d)
    {
        $this->date = $d ?: Carbon::now()->toDateString();
        try {
            \Illuminate\Support\Facades\Log::debug('EphemerisAside: setDateFromEvent dispatching', ['date' => $this->date]);
        } catch (\Throwable $_) {
        }
        try {
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date)->to('object-ephemerides');
            } catch (\Throwable $_) {
            }
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date)->to('moon-details');
            } catch (\Throwable $_) {
            }
            try {
                $this->dispatch('ephemerisDateChanged', date: $this->date)->to('aladin-preview-info');
            } catch (\Throwable $_) {
            }
            $this->dispatch('ephemerisDateChanged', date: $this->date);
        } catch (\Throwable $_) {
            // fallback
        }
        $this->recalculate();
        // Dispatch a lightweight browser event with the freshly computed aside payload
        try {
            $payload = [
                'date' => $this->date,
                'rising' => $this->moon_rise ?? null,
                'setting' => $this->moon_set ?? null,
                'illuminated_fraction' => $this->moon_illuminated ?? null,
                'moon_illumination' => $this->moon_illuminated ?? null,
                'next_new_moon' => $this->next_new_moon ?? null,
                '_ts' => \Carbon\Carbon::now()->toIso8601String(),
            ];
            // Build sanitized payload for object listeners
            $payloadForObjects = $payload;
            if (array_key_exists('illuminated_fraction', $payloadForObjects))
                unset($payloadForObjects['illuminated_fraction']);
            try {
                // See above: dispatch sanitized payload to avoid leaking Moon illum
                $browserPayload = $payloadForObjects;
                $this->dispatchBrowserEvent('dsl-ephemeris-aside-updated', $browserPayload);
            } catch (\Throwable $_) {
            }
            try {
                try {
                    $this->dispatch('ephemerisPayloadUpdated', payload: $payloadForObjects)->to('object-ephemerides');
                } catch (\Throwable $_) {
                    $this->dispatch('ephemerisPayloadUpdated', payload: $payloadForObjects);
                }
                try {
                    \Illuminate\Support\Facades\Log::debug('EphemerisAside: emitTo object-ephemerides ephemerisPayloadUpdated (setDateFromEvent)', ['payload' => $payloadForObjects]);
                } catch (\Throwable $_) {
                }
                try {
                    $this->emitTo('object-ephemerides', 'ephemerisPayloadUpdated', $payloadForObjects);
                } catch (\Throwable $_) {
                }
            } catch (\Throwable $_) {
            }
            // Also send the full moon payload specifically to moon-details
            try {
                $this->emitTo('moon-details', 'ephemerisPayloadUpdated', $payload);
            } catch (\Throwable $_) {
            }
        } catch (\Throwable $_) {
        }
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

                    // Calculate equatorial coords for the Moon using the local
                    // astronomy library. This avoids unnecessary external proxy
                    // calls (which were logging a "Moon" designation) and is
                    // accurate for the Moon. Use the proxy only as a fallback
                    // if the library calculation fails for some reason.
                    try {
                        $moonTarget->calculateEquatorialCoordinates($d->copy(), $geo_coords, $loc->elevation ?? 0.0);
                        // calculate rise/transit/set
                        $moonTarget->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);
                    } catch (\Throwable $_) {
                        try {
                            $proxyResult = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates($moonTarget, $d->copy(), $geo_coords, $loc->elevation ?? 0.0, ['obj' => null]);
                            if (!empty($proxyResult) && !empty($proxyResult['coords'])) {
                                $coords = $proxyResult['coords'];
                                if (method_exists($moonTarget, 'setEquatorialCoordinates')) {
                                    try {
                                        $moonTarget->setEquatorialCoordinates($coords);
                                        $moonTarget->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);
                                    } catch (\Throwable $_) {
                                    }
                                }
                            }
                        } catch (\Throwable $_) {
                            // give up silently
                        }
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

    public function render()
    {
        // Always render the global ephemeris aside. Previously deep-sky
        // object pages suppressed this Livewire component which hid the
        // left aside on pages like M31; we now render it so the ephemeris
        // aside is visible for deep-sky object pages as well.
        return view('livewire.ephemeris-aside');
    }
}
