<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Location;

class SunDetails extends Component
{
    public $objectId;
    public $initial;

    public $sunrise = null;
    public $transit = null;
    public $sunset = null;
    public $nautical_begin = null;
    public $nautical_end = null;
    public $astronomical_begin = null;
    public $astronomical_end = null;

    protected $listeners = [
        'ephemerisDateChanged' => 'handleDateChanged',
        'ephemerisPayloadUpdated' => 'handlePayload',
    ];

    public function mount($objectId = null, $initial = null)
    {
        $this->objectId = $objectId;
        $this->initial = $initial;

        try {
            $p = $initial;
            if (is_array($p)) {
                // accept precomputed strings if supplied by the caller
                if (isset($p['sun_times'])) {
                    [$sr, $ss, $tr] = array_pad(explode(' / ', $p['sun_times']), 3, null);
                    $this->sunrise = $sr ?: $this->sunrise;
                    $this->sunset = $ss ?: $this->sunset;
                    $this->transit = $tr ?: $this->transit;
                }
                if (isset($p['nautical'])) {
                    [$nb, $ne] = array_pad(explode(' / ', $p['nautical']), 2, null);
                    $this->nautical_begin = $nb ?: $this->nautical_begin;
                    $this->nautical_end = $ne ?: $this->nautical_end;
                }
                if (isset($p['astronomical'])) {
                    [$ab, $ae] = array_pad(explode(' / ', $p['astronomical']), 2, null);
                    $this->astronomical_begin = $ab ?: $this->astronomical_begin;
                    $this->astronomical_end = $ae ?: $this->astronomical_end;
                }
            }
        } catch (\Throwable $_) {
        }

        // If we don't have sunrise/sunset values, compute for today or provided date
        if (empty($this->sunrise) || empty($this->sunset)) {
            try {
                $this->recalculateForDate($initial['date'] ?? Carbon::now()->toDateString());
            } catch (\Throwable $_) {
            }
        }
    }

    public function handlePayload($payload = null)
    {
        try {
            $p = $payload ?? [];
            if (isset($p['payload']) && is_array($p['payload'])) $p = $p['payload'];
            if (isset($p['ephemerides']) && is_array($p['ephemerides'])) $p = array_merge($p, $p['ephemerides']);

            if (isset($p['sun_times'])) {
                [$sr, $ss, $tr] = array_pad(explode(' / ', $p['sun_times']), 3, null);
                $this->sunrise = $sr ?: $this->sunrise;
                $this->sunset = $ss ?: $this->sunset;
                $this->transit = $tr ?: $this->transit;
            }
            if (isset($p['nautical'])) {
                [$nb, $ne] = array_pad(explode(' / ', $p['nautical']), 2, null);
                $this->nautical_begin = $nb ?: $this->nautical_begin;
                $this->nautical_end = $ne ?: $this->nautical_end;
            }
            if (isset($p['astronomical'])) {
                [$ab, $ae] = array_pad(explode(' / ', $p['astronomical']), 2, null);
                $this->astronomical_begin = $ab ?: $this->astronomical_begin;
                $this->astronomical_end = $ae ?: $this->astronomical_end;
            }
        } catch (\Throwable $_) {
        }
    }

    public function handleDateChanged($date = null)
    {
        try {
            $d = $date ?: Carbon::now()->toDateString();
            $this->recalculateForDate($d);
        } catch (\Throwable $_) {
        }
    }

    protected function recalculateForDate($date)
    {
        try {
            $user = Auth::user();
            $loc = null;
            if ($user && $user->standardLocation) {
                $loc = $user->standardLocation;
            } else {
                try {
                    $loc = Location::where('active', 1)->first();
                } catch (\Throwable $_) {
                    $loc = null;
                }
            }

            if (! $loc instanceof Location) {
                $this->sunrise = $this->sunrise ?: '-';
                $this->sunset = $this->sunset ?: '-';
                $this->transit = $this->transit ?: '-';
                $this->nautical_begin = $this->nautical_begin ?: '-';
                $this->nautical_end = $this->nautical_end ?: '-';
                $this->astronomical_begin = $this->astronomical_begin ?: '-';
                $this->astronomical_end = $this->astronomical_end ?: '-';
                return;
            }

            try {
                $d = Carbon::createFromFormat('Y-m-d', $date);
            } catch (\Exception $_) {
                $d = Carbon::now();
            }
            $d->hour = 12;

            $timezone = $loc->timezone ?? config('app.timezone');

            $sun_info = date_sun_info($d->timestamp, $loc->latitude, $loc->longitude);

            $tz = $timezone;
            $this->sunrise = (isset($sun_info['sunrise']) && is_int($sun_info['sunrise'])) ? Carbon::createFromTimestamp($sun_info['sunrise'])->timezone($tz)->isoFormat('HH:mm') : '-';
            $this->sunset = (isset($sun_info['sunset']) && is_int($sun_info['sunset'])) ? Carbon::createFromTimestamp($sun_info['sunset'])->timezone($tz)->isoFormat('HH:mm') : '-';
            $this->transit = (isset($sun_info['transit']) && is_int($sun_info['transit'])) ? Carbon::createFromTimestamp($sun_info['transit'])->timezone($tz)->isoFormat('HH:mm') : '-';

            $this->nautical_begin = (isset($sun_info['nautical_twilight_begin']) && is_int($sun_info['nautical_twilight_begin'])) ? Carbon::createFromTimestamp($sun_info['nautical_twilight_begin'])->timezone($tz)->isoFormat('HH:mm') : '-';
            $this->nautical_end = (isset($sun_info['nautical_twilight_end']) && is_int($sun_info['nautical_twilight_end'])) ? Carbon::createFromTimestamp($sun_info['nautical_twilight_end'])->timezone($tz)->isoFormat('HH:mm') : '-';

            $this->astronomical_begin = (isset($sun_info['astronomical_twilight_begin']) && is_int($sun_info['astronomical_twilight_begin'])) ? Carbon::createFromTimestamp($sun_info['astronomical_twilight_begin'])->timezone($tz)->isoFormat('HH:mm') : '-';
            $this->astronomical_end = (isset($sun_info['astronomical_twilight_end']) && is_int($sun_info['astronomical_twilight_end'])) ? Carbon::createFromTimestamp($sun_info['astronomical_twilight_end'])->timezone($tz)->isoFormat('HH:mm') : '-';
        } catch (\Throwable $_) {
            // keep previous values if calculation fails
        }
    }

    public function render()
    {
        return view('livewire.sun-details');
    }
}
