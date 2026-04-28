<?php

namespace App\Models;

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
        'target_type',
        'target_table',
        'target_key',
        'target_name',
        'seeing',
        'limiting_magnitude',
        'sqm',
    ];

    protected function casts(): array
    {
        return [
            'observed_on' => 'date',
            'observed_time' => 'string',
            'has_drawing' => 'boolean',
        ];
    }
}
