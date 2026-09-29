<?php

namespace App\Models;

use App\Models\Traits\DetachesOnDelete;
use App\Models\Traits\HasObservationsDates;
use App\Traits\ClearsResponseCache;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lens extends Model
{
    use ClearsResponseCache;
    use DetachesOnDelete;
    use HasObservationsDates;
    use Sluggable;

    protected $fillable = [
        'user_id',
        'name',
        'factor',
        'make_id',
        'active',
        'observer',
        'picture',
        'description',
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
        return $this->first_observation_date_generic('lensid');
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
        return $this->last_observation_date_generic('lensid');
    }

    /**
     * The users column that holds this as the user's default.
     */
    public function standardUserColumn(): ?string
    {
        return 'stdlens';
    }

    /**
     * Many-to-many relationship to InstrumentSet
     */
    public function instrument_sets()
    {
        return $this->belongsToMany(InstrumentSet::class, 'instrument_set_lens');
    }

    public function fullName(): string
    {
        return ltrim($this->lens_make->name . ' ' . $this->name);
    }
}
