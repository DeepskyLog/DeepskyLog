<?php

namespace App\Models;

use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use App\Traits\ClearsResponseCache;

class Eyepiece extends Model
{
    use Sluggable;
    use ClearsResponseCache;

    protected $fillable = [
        'user_id', 'name', 'make_id', 'type_id', 'focal_length_mm', 'apparentFOV',
        'active', 'observer', 'max_focal_length_mm', 'maxFocalLength', 'field_stop_mm', 'picture',
        'description',
    ];

    protected $with = ['eyepiece_make', 'eyepiece_type'];

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
     * @return BelongsTo the observer this eyepiece belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function eyepiece_make(): BelongsTo
    {
        return $this->belongsTo(EyepieceMake::class, 'make_id');
    }

    // Add a belongsTo relationship to the EyepieceMake model

    public function eyepiece_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\EyepieceType', 'type_id');
    }

    /**
     * Retrieves the date of the first observation made with the eyepiece.
     *
     * This method calculates the earliest observation date for the eyepiece from ObservationsOld
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

        $firstDeepskyObservation = ObservationsOld::where('eyepieceid', $this->id)->min('date');

        if ($firstDeepskyObservation != null) {
            $firstObservation = $firstDeepskyObservation;
        } else {
            return [null, null];
        }

        $date = Carbon::createFromFormat('Ymd', $firstObservation)->locale($language)->isoFormat('LL');

        $id = ObservationsOld::where('eyepieceid', $this->id)->where('date', $firstObservation)->first()['id'];

        return [$date, $id];
    }

    /**
     * Retrieves the date of the last observation made with the eyepiece.
     *
     * This method calculates the last observation date for the eyepiece from ObservationsOld
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

        $lastDeepskyObservation = ObservationsOld::where('eyepieceid', $this->id)->max('date');

        if ($lastDeepskyObservation == null) {
            return [null, null];
        }

        $date = Carbon::createFromFormat('Ymd', $lastDeepskyObservation)->locale($language)->isoFormat('LL');

        $id = ObservationsOld::where('eyepieceid', $this->id)->where('date', $lastDeepskyObservation)->first()['id'];

        return [$date, $id];
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
            $user_slug = User::where('id', $inst->user_id)->pluck('slug')[0];

            $to_return .= "<a href='/instrument/".$user_slug.'/'.$inst->slug."'>".
                $inst->fullName().'</a>'.', ';
        }

        // Remove the trailing comma and space
        return substr($to_return, 0, -2);
    }

    public function get_used_instruments(): Collection
    {
        return ObservationsOld::where('eyepieceid', $this->id)->groupby('instrumentid')->distinct()->pluck('instrumentid');
    }

    /**
     * Returns the full name of the eyepiece.
     *
     * @return string the name of the eyepiece
     */
    public function fullName(): string
    {
        return trim($this->name);
    }

    /**
     * Many-to-many relationship to InstrumentSet
     */
    public function instrument_sets()
    {
        return $this->belongsToMany(InstrumentSet::class, 'instrument_set_eyepiece');
    }
}
