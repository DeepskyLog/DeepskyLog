<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserObjectMetric extends Model
{
    protected $table = 'user_object_metrics';

    protected $fillable = [
        'user_id',
        'lens_id',
        'instrument_id',
        'location_id',
        'object_name',
        'contrast_reserve',
        'contrast_reserve_category',
        'optimum_detection_magnification',
        'optimum_eyepieces',
    ];

    protected $casts = [
        'contrast_reserve' => 'float',
        'optimum_eyepieces' => 'array',
    ];
}
