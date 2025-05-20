<?php

namespace App\Models;

use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lens extends Model
{
    use Sluggable;

    protected $fillable = [
        'user_id', 'name', 'factor', 'make_id', 'active', 'observer', 'picture',
    ];

    protected $with = ['lens_make'];

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
     * @return BelongsTo the observer this lens belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function lens_make(): BelongsTo
    {
        return $this->belongsTo(LensMake::class, 'make_id');
    }

    /**
     * Retrieves the date of the first observation made with the lens.
     *
     * This method calculates the earliest observation date for the lens from ObservationsOld
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

        $firstObservation = ObservationsOld::where('lensid', $this->id)->min('date');

        if ($firstObservation == null) {
            return [null, null];
        }

        $date = Carbon::createFromFormat('Ymd', $firstObservation)->locale($language)->isoFormat('LL');

        $id = ObservationsOld::where('lensid', $this->id)->where('date', $firstObservation)->first()['id'];

        return [$date, $id];
    }

    /**
     * Retrieves the date of the last observation made with the lens.
     *
     * This method calculates the last observation date for the lens from ObservationsOld
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

        $lastObservation = ObservationsOld::where('lensid', $this->id)->max('date');

        if ($lastObservation == null) {
            return [null, null];
        }

        $date = Carbon::createFromFormat('Ymd', $lastObservation)->locale($language)->isoFormat('LL');
        $id = ObservationsOld::where('lensid', $this->id)->where('date', $lastObservation)->first()['id'];

        return [$date, $id];
    }
}
