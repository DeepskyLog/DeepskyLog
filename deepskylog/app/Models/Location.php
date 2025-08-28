<?php

namespace App\Models;

use App\Models\Traits\HasObservationsDates;
use Cviebrock\EloquentSluggable\Sluggable;
use deepskylog\AstronomyLibrary\Magnitude;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
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
}
