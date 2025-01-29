<?php

namespace App\Models;

use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Instrument extends Model
{
    use Sluggable;

    protected $fillable = [
        'user_id', 'name', 'aperture_mm', 'type', 'instrument_type_id', 'make_id', 'mount_type_id',
        'focal_length_mm', 'fixedMagnification', 'active', 'observer', 'flip_image', 'flop_image',
        'obstruction_perc', 'picture',
    ];

    protected $with = ['make', 'mount_type', 'instrument_type'];

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

    /**
     * Returns the name of the instrument type.
     *
     * @return string the name of the instrument type
     */
    public function fullName(): string
    {
        return ltrim($this->make->name.' '.$this->name);
    }

    public function make(): BelongsTo
    {
        return $this->belongsTo('App\Models\InstrumentMake');
    }

    public function mount_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\MountType');
    }

    public function instrument_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\InstrumentType');
    }

    /**
     * Retrieves the date of the first observation made with the instrument.
     *
     * This method calculates the earliest observation date for the instrument from two sources:
     * - ObservationsOld
     * - CometObservationsOld
     *
     * It compares the minimum dates from both sources and returns the earliest one.
     * If no observations are found, it returns [null, null].
     *
     * The date is formatted according to the current application locale.
     * Additionally, the method retrieves the ID of the first observation.
     *
     * @return array An array containing the formatted date of the first observation and its ID.
     */
    public function first_observation_date(): array
    {
        $language = app()->getLocale();

        $firstDeepskyObservation = ObservationsOld::where('instrumentid', $this->id)->min('date');
        $firstCometObservation = CometObservationsOld::where('instrumentid', $this->id)->min('date');

        if ($firstDeepskyObservation == null && $firstCometObservation != null) {
            $firstObservation = $firstCometObservation;
        } elseif ($firstDeepskyObservation != null && $firstCometObservation == null) {
            $firstObservation = $firstDeepskyObservation;
        } elseif ($firstDeepskyObservation == null && $firstCometObservation == null) {
            return [null, null];
        } else {
            $firstObservation = min($firstDeepskyObservation, $firstCometObservation);
        }

        $date = Carbon::createFromFormat('Ymd', $firstObservation)->locale($language)->isoFormat('LL');

        if ($firstObservation == $firstDeepskyObservation) {
            $id = ObservationsOld::where('instrumentid', $this->id)->where('date', $firstObservation)->first()['id'];
        } else {
            $id = -CometObservationsOld::where('instrumentid', $this->id)->where('date', $firstObservation)->first()['id'];
        }

        return [$date, $id];
    }

    /**
     * Retrieves the date of the last observation made with the instrument.
     *
     * This method calculates the last observation date for the instrument from two sources:
     * - ObservationsOld
     * - CometObservationsOld
     *
     * It compares the maximum dates from both sources and returns the last one.
     * If no observations are found, it returns [null, null].
     *
     * The date is formatted according to the current application locale.
     * Additionally, the method retrieves the ID of the last observation.
     *
     * @return array An array containing the formatted date of the last observation and its ID.
     */
    public function last_observation_date(): array
    {
        $language = app()->getLocale();

        $lastDeepskyObservation = ObservationsOld::where('instrumentid', $this->id)->max('date');
        $lastCometObservation = CometObservationsOld::where('instrumentid', $this->id)->max('date');

        $lastObservation = max($lastDeepskyObservation, $lastCometObservation);

        if ($lastObservation == null) {
            return [null, null];
        }

        $date = Carbon::createFromFormat('Ymd', $lastObservation)->locale($language)->isoFormat('LL');
        if ($lastObservation == $lastDeepskyObservation) {
            $id = ObservationsOld::where('instrumentid', $this->id)->where('date', $lastObservation)->first()['id'];
        } else {
            $id = -CometObservationsOld::where('instrumentid', $this->id)->where('date', $lastObservation)->first()['id'];
        }

        return [$date, $id];
    }

    public function get_used_eyepieces_as_string(): string
    {
        $eyepieces = $this->get_used_eyepieces();

        $to_return = '';

        foreach ($eyepieces as $eyepiece) {
            if ($eyepiece == 0) {
                continue;
            }
            $to_return .= "<a href='".config('app.old_url').'index.php?indexAction=detail_eyepiece&eyepiece='.$eyepiece."'>".
                EyepiecesOld::where('id', $eyepiece)->pluck('name')[0].'</a>'.', ';
        }

        // Remove the trailing comma and space
        return substr($to_return, 0, -2);
    }

    public function get_used_eyepieces(): Collection
    {
        return ObservationsOld::where('instrumentid', $this->id)->groupby('eyepieceid')->distinct()->pluck('eyepieceid');
    }

    public function get_used_filters_as_string(): string
    {
        $filters = $this->get_used_filters();

        $to_return = '';

        foreach ($filters as $filter) {
            if ($filter == 0) {
                continue;
            }
            $to_return .= "<a href='".config('app.old_url').'index.php?indexAction=detail_filter&filter='.$filter."'>".
                FiltersOld::where('id', $filter)->pluck('name')[0].'</a>'.', ';
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
            $to_return .= "<a href='".config('app.old_url').'index.php?indexAction=detail_lens&lens='.$lens."'>".
                LensesOld::where('id', $lens)->pluck('name')[0].'</a>'.', ';
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
            $to_return .= "<a href='".config('app.old_url').'index.php?indexAction=detail_location&location='.$location."'>".
                LocationsOld::where('id', $location)->pluck('name')[0].'</a>'.', ';
        }

        // Remove the trailing comma and space
        return substr($to_return, 0, -2);
    }

    public function get_used_locations(): Collection
    {
        return ObservationsOld::where('instrumentid', $this->id)->groupby('locationid')->distinct()->pluck('locationid');
    }
}
