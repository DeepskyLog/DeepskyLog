<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Eyepiece extends Model
{
    use ClearsResponseCache;
    use Sluggable;

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

        // Prefer a request-scoped bulk map when available to avoid per-eyepiece queries
        if (isset(self::$bulkFirstObservationMap) && array_key_exists($this->id, self::$bulkFirstObservationMap)) {
            $entry = self::$bulkFirstObservationMap[$this->id];
            if (! $entry) return [null, null];
            $firstObservation = $entry['date'];
            $id = $entry['id'];
            $date = Carbon::createFromFormat('Ymd', (string) $firstObservation)->locale($language)->isoFormat('LL');
            return [$date, $id];
        }

        $firstDeepskyObservation = ObservationsOld::where('eyepieceid', $this->id)->min('date');

        if ($firstDeepskyObservation == null) {
            return [null, null];
        }

        $firstObservation = $firstDeepskyObservation;
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

        // Prefer a request-scoped bulk map when available to avoid per-eyepiece queries
        if (isset(self::$bulkLastObservationMap) && array_key_exists($this->id, self::$bulkLastObservationMap)) {
            $entry = self::$bulkLastObservationMap[$this->id];
            if (! $entry) return [null, null];
            $lastObservation = $entry['date'];
            $id = $entry['id'];
            $date = Carbon::createFromFormat('Ymd', (string) $lastObservation)->locale($language)->isoFormat('LL');
            return [$date, $id];
        }

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
        // If a bulk map has been provided for this request, prefer it to avoid
        // issuing a separate DB query per eyepiece (removes N+1 behaviour).
        if (isset(self::$bulkUsedInstrumentsMap) && is_array(self::$bulkUsedInstrumentsMap) && array_key_exists($this->id, self::$bulkUsedInstrumentsMap)) {
            return collect(self::$bulkUsedInstrumentsMap[$this->id]);
        }

        // Fast-path: if the authenticated user has a standard instrument set
        // or a selected standard instrument, restrict calculations to that
        // instrument only. This avoids many legacy per-eyepiece queries
        // against the old `observations` table when the user expects
        // calculations only for their default instrument.
        try {
            $authUser = auth()->user();
            if ($authUser && ! empty($authUser->standardInstrument)) {
                $inst = $authUser->standardInstrument;
                if ($inst && isset($inst->id)) {
                    return collect([$inst->id]);
                }
            }
        } catch (\Throwable $_) {
            // ignore and fall back to legacy behaviour
        }

        return ObservationsOld::where('eyepieceid', $this->id)->groupby('instrumentid')->distinct()->pluck('instrumentid');
    }

    // Request-scoped bulk map of eyepiece_id => array of instrument ids.
    protected static array $bulkUsedInstrumentsMap = [];

    // Request-scoped bulk map eyepiece_id => ['date'=>..., 'id'=>...] for first/last observations
    protected static array $bulkFirstObservationMap = [];
    protected static array $bulkLastObservationMap = [];

    public static function setBulkUsedInstrumentsMap(array $map): void
    {
        self::$bulkUsedInstrumentsMap = $map;
    }

    public static function setBulkFirstObservationMap(array $map): void
    {
        self::$bulkFirstObservationMap = $map;
    }

    public static function setBulkLastObservationMap(array $map): void
    {
        self::$bulkLastObservationMap = $map;
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
