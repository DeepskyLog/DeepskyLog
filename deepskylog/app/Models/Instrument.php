<?php

namespace App\Models;

use App\Models\Traits\HasObservationsDates;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Instrument extends Model
{
    use HasObservationsDates;
    use Sluggable;

    protected $fillable = [
        'user_id', 'name', 'aperture_mm', 'type', 'instrument_type_id', 'make_id', 'mount_type_id',
        'focal_length_mm', 'fixedMagnification', 'active', 'observer', 'flip_image', 'flop_image',
        'obstruction_perc', 'picture', 'description',
    ];

    protected $with = ['instrument_make', 'mount_type', 'instrument_type'];

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
     * @return BelongsTo the observer this instrument belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function instrument_make(): BelongsTo
    {
        return $this->belongsTo(InstrumentMake::class, 'make_id');
    }

    // Add a belongsTo relationship to the InstrumentMake model

    public function mount_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\MountType');
    }

    public function instrument_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\InstrumentType');
    }

    public function first_observation_date(): array
    {
        return $this->first_observation_date_generic('instrumentid');
    }

    public function last_observation_date(): array
    {
        return $this->last_observation_date_generic('instrumentid');
    }

    public function get_used_eyepieces_as_string(): string
    {
        $eyepieces = $this->get_used_eyepieces();

        $to_return = '';

        foreach ($eyepieces as $eyepiece) {
            if ($eyepiece == 0) {
                continue;
            }
            $ep = Eyepiece::where('id', $eyepiece)->first();
            $to_return .= "<a href='/eyepiece/".$ep->user->slug.'/'.$ep->slug."'>".
                $ep->fullName().'</a>'.', ';
        }

        // Remove the trailing comma and space
        return substr($to_return, 0, -2);
    }

    public function get_used_eyepieces(): Collection
    {
        return ObservationsOld::where('instrumentid', $this->id)->groupby('eyepieceid')->distinct()->pluck('eyepieceid');
    }

    /**
     * Returns the name of the instrument type.
     *
     * @return string the name of the instrument type
     */
    public function fullName(): string
    {
        return ltrim($this->instrument_make->name.' '.$this->name);
    }

    public function get_used_filters_as_string(): string
    {
        $filters = $this->get_used_filters();

        $to_return = '';

        foreach ($filters as $filter) {
            if ($filter == 0) {
                continue;
            }
            $filt = Filter::where('id', $filter)->first();
            $to_return .= "<a href='/filter/".$filt->user->slug.'/'.$filt->slug."'>".
                $filt->name.'</a>'.', ';
        }

        // Remove the trailing comma and space
        return substr($to_return, 0, -2);
    }

    public function get_used_filters(): Collection
    {
        return ObservationsOld::where('instrumentid', $this->id)->groupby('filterid')->distinct()->pluck('filterid');
    }

    public function get_used_lenses_as_string(): string
    {
        $lenses = $this->get_used_lenses();

        $to_return = '';

        foreach ($lenses as $lens) {
            if ($lens == 0) {
                continue;
            }
            $lns = Lens::where('id', $lens)->first();
            $to_return .= "<a href='/lens/".$lns->user->slug.'/'.$lns->slug."'>".
                $lns->name.'</a>'.', ';
        }

        // Remove the trailing comma and space
        return substr($to_return, 0, -2);
    }

    public function get_used_lenses(): Collection
    {
        return ObservationsOld::where('instrumentid', $this->id)->groupby('lensid')->distinct()->pluck('lensid');
    }

    public function get_used_locations_as_string(): string
    {
        $locations = $this->get_used_locations();

        $to_return = '';

        foreach ($locations as $location) {
            if ($location == 0) {
                continue;
            }
            $loc = Location::where('id', $location)->first();
            $to_return .= "<a href='/location/".$loc->user->slug.'/'.$loc->slug."'>".
                $loc->name.'</a>'.', ';
        }

        // Remove the trailing comma and space
        return substr($to_return, 0, -2);
    }

    public function get_used_locations(): Collection
    {
        return ObservationsOld::where('instrumentid', $this->id)->groupby('locationid')->distinct()->pluck('locationid');
    }

    public function magnification(Eyepiece $eyepiece, ?Lens $lens = null): string
    {
        if ($lens) {
            return round($this->focal_length_mm * $lens->factor / ($eyepiece->focal_length_mm)).'x';
        }

        return round($this->focal_length_mm / $eyepiece->focal_length_mm).'x';
    }

    public function field_of_view(Eyepiece $eyepiece, ?Lens $lens = null): string
    {
        $focal_length = $this->focal_length_mm;
        if ($lens) {
            $focal_length = $this->focal_length_mm * $lens->factor;
        }
        if ($eyepiece->field_stop_mm != 0) {
            $tfov = $eyepiece->field_stop_mm / $focal_length * 57.2958;
            // Convert $tfov to degrees and minutes and return as a string
            $degrees = floor($tfov);
            $minutes = round(($tfov - $degrees) * 60);
            if ($minutes < 10) {
                $minutes = '0'.$minutes;
            }
            $tfov = $degrees.'° '.$minutes."'";
        } elseif ($eyepiece->apparentFOV > 10) {
            // Calculate the true field of view
            $tfov = $eyepiece->apparentFOV / ($focal_length / $eyepiece->focal_length_mm);
            // Convert $tfov to degrees and minutes and return as a string
            $degrees = floor($tfov);
            $minutes = round(($tfov - $degrees) * 60);
            // Add a 0 to the minutes if it is less than 10
            if ($minutes < 10) {
                $minutes = '0'.$minutes;
            }
            $tfov = $degrees.'° '.$minutes."'";
        } else {
            $tfov = __('Unknown');
        }

        return $tfov;
    }

    public function exit_pupil(Eyepiece $eyepiece, ?Lens $lens = null): string
    {
        if ($lens) {
            return round($this->aperture_mm / ($this->focal_length_mm * $lens->factor / $eyepiece->focal_length_mm), 1).'mm';
        }

        return round($this->aperture_mm / ($this->focal_length_mm / $eyepiece->focal_length_mm), 1).'mm';
    }

    /**
     * Many-to-many relationship to InstrumentSet
     */
    public function instrument_sets()
    {
        return $this->belongsToMany(InstrumentSet::class, 'instrument_set_instrument');
    }
}
