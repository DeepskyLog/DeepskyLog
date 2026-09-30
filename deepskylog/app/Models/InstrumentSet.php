<?php

namespace App\Models;

use App\Models\Traits\OwnedByUser;
use App\Traits\ClearsResponseCache;
use App\Traits\HasUniqueSlugPerUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InstrumentSet extends Model
{
    use ClearsResponseCache;
    use HasFactory;
    use HasUniqueSlugPerUser;
    use OwnedByUser;

    protected $fillable = [
        'user_id', 'name', 'description', 'active', 'picture',
    ];

    protected $with = [];

    /**
     * Detach the pivot rows (their foreign keys have no cascade) and clear the
     * default of the users that have this set as their standard set.
     */
    protected static function booted()
    {
        static::deleting(function (self $set) {
            $set->instruments()->detach();
            $set->locations()->detach();
            $set->eyepieces()->detach();
            $set->filters()->detach();
            $set->lenses()->detach();

            User::where('stdinstrumentset', $set->id)->update(['stdinstrumentset' => null]);
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => true,
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
