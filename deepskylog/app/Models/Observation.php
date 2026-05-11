<?php

namespace App\Models;

use deepskylog\AstronomyLibrary\Magnitude;
use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    protected $table = 'observations';

    protected $fillable = [
        'legacy_source',
        'legacy_id',
        'observer_username',
        'observer_user_id',
        'instrument_id',
        'location_id',
        'eyepiece_id',
        'filter_id',
        'lens_id',
        'observed_on',
        'observed_time',
        'legacy_date_int',
        'legacy_time_int',
        'legacy_timestamp',
        'description',
        'language',
        'has_drawing',
        'drawing_filename',
        'target_type',
        'target_table',
        'target_key',
        'target_name',
        'seeing',
        'transparency',
        'limiting_magnitude',
        'sqm',
    ];

    protected function casts(): array
    {
        return [
            'observed_on' => 'date',
            'observed_time' => 'string',
            'seeing' => 'integer',
            'transparency' => 'integer',
            'has_drawing' => 'boolean',
        ];
    }

    public static function getObservationsCountForObject(string $targetTable, string $targetKey): int
    {
        if (empty($targetTable) || empty($targetKey)) {
            return 0;
        }

        return static::where('target_table', $targetTable)
            ->where('target_key', $targetKey)
            ->count();
    }

    public static function getDrawingsCountForObject(string $targetTable, string $targetKey): int
    {
        if (empty($targetTable) || empty($targetKey)) {
            return 0;
        }

        return static::where('target_table', $targetTable)
            ->where('target_key', $targetKey)
            ->where('has_drawing', 1)
            ->count();
    }

    public static function getObservationsCountForUser(?\App\Models\User $user = null, ?string $targetTable = null, ?string $targetKey = null): int
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return 0;
        }

        $query = static::where('observer_user_id', $user->id);
        if ($targetTable) {
            $query->where('target_table', $targetTable);
        }
        if ($targetKey) {
            $query->where('target_key', $targetKey);
        }

        return $query->count();
    }

    public static function getDrawingsCountForUser(?\App\Models\User $user = null, ?string $targetTable = null, ?string $targetKey = null): int
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return 0;
        }

        $query = static::where('observer_user_id', $user->id)->where('has_drawing', 1);
        if ($targetTable) {
            $query->where('target_table', $targetTable);
        }
        if ($targetKey) {
            $query->where('target_key', $targetKey);
        }

        return $query->count();
    }

    /**
     * Backwards-compatible accessor for legacy templates that expect `SQM`.
     * Returns stored `sqm` or computes it from `limiting_magnitude` when possible.
     */
    public function getSQMAttribute()
    {
        if (array_key_exists('sqm', $this->attributes) && $this->attributes['sqm'] !== null) {
            return (float) $this->attributes['sqm'];
        }

        if (array_key_exists('limiting_magnitude', $this->attributes) && $this->attributes['limiting_magnitude'] !== null) {
            try {
                return round(Magnitude::nelmToSqm((float) $this->attributes['limiting_magnitude']), 2);
            } catch (\Throwable $_) {
                return null;
            }
        }

        return null;
    }

    /**
     * Backwards-compatible accessor for legacy templates that expect `limmag`.
     * Returns stored `limiting_magnitude` or computes it from `sqm` when possible.
     */
    public function getLimmagAttribute()
    {
        if (array_key_exists('limiting_magnitude', $this->attributes) && $this->attributes['limiting_magnitude'] !== null) {
            return (float) $this->attributes['limiting_magnitude'];
        }

        if (array_key_exists('sqm', $this->attributes) && $this->attributes['sqm'] !== null) {
            try {
                return round(Magnitude::sqmToNelm((float) $this->attributes['sqm']), 1);
            } catch (\Throwable $_) {
                return null;
            }
        }

        return null;
    }

    /**
     * Compute a Bortle value when possible from SQM or limiting magnitude.
     */
    public function getBortleAttribute()
    {
        if (array_key_exists('sqm', $this->attributes) && $this->attributes['sqm'] !== null) {
            try {
                return Magnitude::sqmToBortle((float) $this->attributes['sqm']);
            } catch (\Throwable $_) {
                // fall through
            }
        }

        if (array_key_exists('limiting_magnitude', $this->attributes) && $this->attributes['limiting_magnitude'] !== null) {
            try {
                return Magnitude::nelmToBortle((float) $this->attributes['limiting_magnitude']);
            } catch (\Throwable $_) {
                // fall through
            }
        }

        return null;
    }
}
