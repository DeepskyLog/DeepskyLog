<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A naked-eye limiting magnitude measured by counting the stars in a figure
 * of bright stars (App\Services\StarCountAreas, Livewire SkyStarCount): the
 * figure (`area`), the stars counted, the limit in the figure, its altitude
 * and the limit at the zenith (`nelm`) at measured_at (UTC). The sky history
 * of the location (LocationSkyHistory) counts it as a measured night.
 */
class SkyMeasurement extends Model
{
    protected $fillable = ['user_id', 'location_id', 'measured_at', 'area', 'star_count', 'area_magnitude', 'altitude', 'nelm'];

    protected $casts = [
        'measured_at' => 'datetime',
        'star_count' => 'integer',
        'area_magnitude' => 'float',
        'altitude' => 'float',
        'nelm' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
