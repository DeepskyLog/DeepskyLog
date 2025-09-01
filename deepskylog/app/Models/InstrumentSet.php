<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InstrumentSet extends Model
{
    use HasFactory;
    use Sluggable;

    protected $fillable = [
        'user_id', 'name', 'description', 'active', 'picture',
    ];

    protected $with = [];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => false,
            ],
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function instruments(): BelongsToMany
    {
        return $this->belongsToMany(Instrument::class, 'instrument_set_instrument');
    }

    public function eyepieces(): BelongsToMany
    {
        return $this->belongsToMany(Eyepiece::class, 'instrument_set_eyepiece');
    }

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class, 'instrument_set_filter');
    }

    public function lenses(): BelongsToMany
    {
        return $this->belongsToMany(Lens::class, 'instrument_set_lens');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'instrument_set_location');
    }
}
