<?php

namespace App\Models;

use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Filter extends Model
{
    use Sluggable;

    protected $fillable = [
        'user_id', 'name', 'make_id', 'type_id', 'color_id', 'wratten', 'schott', 'active', 'observer', 'picture', 'description',
    ];

    protected $with = ['filter_make', 'filter_color', 'filter_type'];

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
     * @return BelongsTo the observer this filter belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function filter_make(): BelongsTo
    {
        return $this->belongsTo(FilterMake::class, 'make_id');
    }

    public function filter_type(): BelongsTo
    {
        return $this->belongsTo(FilterType::class, 'type_id');
    }

    public function filter_color(): BelongsTo
    {
        return $this->belongsTo(FilterColor::class, 'color_id');
    }

    /**
     * Retrieves the date of the first observation made with the filter.
     *
     * This method calculates the earliest observation date for the filter from ObservationsOld
     *
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

        $firstObservation = ObservationsOld::where('filterid', $this->id)->min('date');

        if ($firstObservation == null) {
            return [null, null];
        }

        $date = Carbon::createFromFormat('Ymd', $firstObservation)->locale($language)->isoFormat('LL');

        $id = ObservationsOld::where('filterid', $this->id)->where('date', $firstObservation)->first()['id'];

        return [$date, $id];
    }

    /**
     * Retrieves the date of the last observation made with the filter.
     *
     * This method calculates the last observation date for the filter from ObservationsOld
     *
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

        $lastObservation = ObservationsOld::where('filterid', $this->id)->max('date');

        if ($lastObservation == null) {
            return [null, null];
        }

        $date = Carbon::createFromFormat('Ymd', $lastObservation)->locale($language)->isoFormat('LL');
        $id = ObservationsOld::where('filterid', $this->id)->where('date', $lastObservation)->first()['id'];

        return [$date, $id];
    }

    /**
     * Many-to-many relationship to InstrumentSet
     */
    public function instrument_sets()
    {
        return $this->belongsToMany(InstrumentSet::class, 'instrument_set_filter');
    }

    
    public function fullName(): string
    {
        return ltrim($this->filter_make->name.' '.$this->name);
    }
}
