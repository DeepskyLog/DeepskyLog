<?php

namespace App\Models;

use App\Models\Traits\HasObservationsDates;
use App\Traits\ClearsResponseCache;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Magnitude;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Session;

class Location extends Model
{
    use ClearsResponseCache;
    use HasObservationsDates;
    use Sluggable;

    protected $fillable = [
        'user_id', 'name', 'longitude', 'latitude', 'country', 'timezone', 'limitingMagnitude', 'skyBackground',
        'elevation', 'active', 'observer', 'picture', 'hidden',
        'description',
    ];

    protected $casts = [
        'skyBackground' => 'float',
        'limitingMagnitude' => 'float',
        'elevation' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'active' => 'boolean',
        'hidden' => 'boolean',
    ];

    /**
     * Convert decimal degrees to DMS format with direction.
     */
    public static function dms(float $decimal, bool $isLat = true): string
    {
        $dir = $isLat ? ($decimal >= 0 ? __('N') : __('S')) : ($decimal >= 0 ? __('E') : __('W'));
        $abs = abs($decimal);
        $deg = intval($abs);
        $minFloat = ($abs - $deg) * 60;
        $min = intval($minFloat);
        $sec = round(($minFloat - $min) * 60, 2);

        return sprintf("%d° %d' %.2f\" %s", $deg, $min, $sec, $dir);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => false,
            ],
        ];
    }

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this location belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function first_observation_date(): array
    {
        return $this->first_observation_date_generic('locationid');
    }

    public function get_used_instruments_as_string(): string
    {
        $instruments = $this->get_used_instruments();

        $to_return = '';

        foreach ($instruments as $instrument) {
            if ($instrument == 0) {
                continue;
            }
            $inst = Instrument::where('id', $instrument)->first();
            $to_return .= "<a href='/instrument/".$inst->user->slug.'/'.$inst->slug."'>".
                $inst->fullName().'</a>'.', ';
        }

        // Remove the trailing comma and space
        return substr($to_return, 0, -2);
    }

    public function get_used_instruments()
    {
        return ObservationsOld::where('locationid', $this->id)->groupby('instrumentid')->distinct()->pluck('instrumentid');
    }

    public function last_observation_date(): array
    {
        return $this->last_observation_date_generic('locationid');
    }

    /**
     * Get SQM value, calculated from limitingMagnitude if needed.
     */
    public function getSqm($fstOffset = 0)
    {
        if ($this->skyBackground > 0) {
            return $this->skyBackground;
        } elseif ($this->limitingMagnitude > 0) {
            return round(Magnitude::nelmToSqm($this->limitingMagnitude, $fstOffset), 2);
        } else {
            return null;
        }
    }

    /**
     * Get NELM value, calculated from SQM if needed.
     */
    public function getNelm($fstOffset = 0)
    {
        if ($this->limitingMagnitude > 0) {
            return $this->limitingMagnitude;
        } elseif ($this->skyBackground > 0) {
            return round(Magnitude::sqmToNelm($this->skyBackground, $fstOffset), 1);
        } else {
            return null;
        }
    }

    /**
     * Get Bortle value, calculated from SQM or NELM.
     */
    public function getBortle()
    {
        if ($this->skyBackground > 0) {
            return Magnitude::sqmToBortle($this->skyBackground);
        } elseif ($this->limitingMagnitude > 0) {
            return Magnitude::nelmToBortle($this->limitingMagnitude);
        } else {
            return null;
        }
    }

    /**
     * Return an image (HTML <img>) with the length of night plot for this location.
     * The method reads the current 'date' from session (format Y-m-d) and falls
     * back to today when missing or invalid.
     *
     * @return string HTML string containing an <img> tag with the plot
     */
    public function getLengthOfNightPlot(): string
    {
        // Try to use the session date if present, otherwise use today
        $datestr = Session::get('date', Carbon::now()->format('Y-m-d'));
        try {
            $date = Carbon::createFromFormat('Y-m-d', $datestr);
        } catch (\Exception $e) {
            $date = Carbon::now();
        }

        // Use local midday to avoid DST/day boundary issues
        $date->hour = 12;

        $coords = new GeographicalCoordinates($this->longitude, $this->latitude);

        // Pass elevation if available, else 0.0
        $astrolib = new AstronomyLibrary($date, $coords, $this->elevation ?? 0.0);

        // Use the location timezone if set, otherwise fallback to app timezone
        $timezone = $this->timezone ?? config('app.timezone');

        return $astrolib->getLengthOfNightPlot($timezone);
    }

    /**
     * Returns today's sunrise / sunset / transit times for this location as a formatted string.
     * Uses the session 'date' (Y-m-d) when available, otherwise uses today.
     *
     * @return string e.g. "06:23 / 21:45 / 13:05" or "- / - / -" when unavailable
     */
    public function sunriseSetTransit(): string
    {
        $datestr = Session::get('date', Carbon::now()->format('Y-m-d'));
        try {
            $date = Carbon::createFromFormat('Y-m-d', $datestr);
        } catch (\Exception $e) {
            $date = Carbon::now();
        }

        // Use midday to avoid boundary/DST issues
        $date->hour = 12;

        $sun_info = date_sun_info(
            $date->timestamp,
            $this->latitude,
            $this->longitude
        );

        $timezone = $this->timezone ?? config('app.timezone');

        // Sunrise
        if (! isset($sun_info['sunrise']) || $sun_info['sunrise'] === true || $sun_info['sunrise'] === false) {
            $sunrise = '-';
        } else {
            $sunrise = Carbon::createFromTimestamp($sun_info['sunrise'])->timezone($timezone)->isoFormat('HH:mm');
        }

        // Sunset
        if (! isset($sun_info['sunset']) || $sun_info['sunset'] === true || $sun_info['sunset'] === false) {
            $sunset = '-';
        } else {
            $sunset = Carbon::createFromTimestamp($sun_info['sunset'])->timezone($timezone)->isoFormat('HH:mm');
        }

        // Transit
        if (! isset($sun_info['transit']) || $sun_info['transit'] === true || $sun_info['transit'] === false) {
            $transit = '-';
        } else {
            $transit = Carbon::createFromTimestamp($sun_info['transit'])->timezone($timezone)->isoFormat('HH:mm');
        }

        return $sunrise.' / '.$sunset.' / '.$transit;
    }

    /**
     * Returns today's civil twilight end / begin times for this location as a formatted string.
     * Uses the session 'date' (Y-m-d) when available, otherwise uses today.
     *
     * @return string e.g. "21:00 / 04:30" or "- / -" when unavailable
     */
    public function civilTwilight(): string
    {
        $datestr = Session::get('date', Carbon::now()->format('Y-m-d'));
        try {
            $date = Carbon::createFromFormat('Y-m-d', $datestr);
        } catch (\Exception $e) {
            $date = Carbon::now();
        }

        // Use midday to avoid boundary/DST issues
        $date->hour = 12;

        $sun_info = date_sun_info(
            $date->timestamp,
            $this->latitude,
            $this->longitude
        );

        $timezone = $this->timezone ?? config('app.timezone');

        if (! isset($sun_info['civil_twilight_end']) || $sun_info['civil_twilight_end'] === true || $sun_info['civil_twilight_end'] === false) {
            $end = '-';
        } else {
            $end = Carbon::createFromTimestamp($sun_info['civil_twilight_end'])->timezone($timezone)->isoFormat('HH:mm');
        }

        if (! isset($sun_info['civil_twilight_begin']) || $sun_info['civil_twilight_begin'] === true || $sun_info['civil_twilight_begin'] === false) {
            $start = '-';
        } else {
            $start = Carbon::createFromTimestamp($sun_info['civil_twilight_begin'])->timezone($timezone)->isoFormat('HH:mm');
        }

        return $end.' / '.$start;
    }

    /**
     * Returns today's nautical twilight end / begin times for this location as a formatted string.
     * Uses the session 'date' (Y-m-d) when available, otherwise uses today.
     *
     * @return string e.g. "22:30 / 03:45" or "- / -" when unavailable
     */
    public function nauticalTwilight(): string
    {
        $datestr = Session::get('date', Carbon::now()->format('Y-m-d'));
        try {
            $date = Carbon::createFromFormat('Y-m-d', $datestr);
        } catch (\Exception $e) {
            $date = Carbon::now();
        }

        $date->hour = 12;

        $sun_info = date_sun_info(
            $date->timestamp,
            $this->latitude,
            $this->longitude
        );

        $timezone = $this->timezone ?? config('app.timezone');

        if (! isset($sun_info['nautical_twilight_end']) || $sun_info['nautical_twilight_end'] === true || $sun_info['nautical_twilight_end'] === false) {
            $end = '-';
        } else {
            $end = Carbon::createFromTimestamp($sun_info['nautical_twilight_end'])->timezone($timezone)->isoFormat('HH:mm');
        }

        if (! isset($sun_info['nautical_twilight_begin']) || $sun_info['nautical_twilight_begin'] === true || $sun_info['nautical_twilight_begin'] === false) {
            $start = '-';
        } else {
            $start = Carbon::createFromTimestamp($sun_info['nautical_twilight_begin'])->timezone($timezone)->isoFormat('HH:mm');
        }

        return $end.' / '.$start;
    }

    /**
     * Returns today's astronomical twilight end / begin times for this location as a formatted string.
     * Uses the session 'date' (Y-m-d) when available, otherwise uses today.
     *
     * @return string e.g. "23:10 / 02:50" or "- / -" when unavailable
     */
    public function astronomicalTwilight(): string
    {
        $datestr = Session::get('date', Carbon::now()->format('Y-m-d'));
        try {
            $date = Carbon::createFromFormat('Y-m-d', $datestr);
        } catch (\Exception $e) {
            $date = Carbon::now();
        }

        $date->hour = 12;

        $sun_info = date_sun_info(
            $date->timestamp,
            $this->latitude,
            $this->longitude
        );

        $timezone = $this->timezone ?? config('app.timezone');

        if (! isset($sun_info['astronomical_twilight_end']) || $sun_info['astronomical_twilight_end'] === true || $sun_info['astronomical_twilight_end'] === false) {
            $end = '-';
        } else {
            $end = Carbon::createFromTimestamp($sun_info['astronomical_twilight_end'])->timezone($timezone)->isoFormat('HH:mm');
        }

        if (! isset($sun_info['astronomical_twilight_begin']) || $sun_info['astronomical_twilight_begin'] === true || $sun_info['astronomical_twilight_begin'] === false) {
            $start = '-';
        } else {
            $start = Carbon::createFromTimestamp($sun_info['astronomical_twilight_begin'])->timezone($timezone)->isoFormat('HH:mm');
        }

        return $end.' / '.$start;
    }

    /**
     * Many-to-many relationship to InstrumentSet
     */
    public function instrument_sets()
    {
        return $this->belongsToMany(InstrumentSet::class, 'instrument_set_location');
    }
}
