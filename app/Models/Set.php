<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/** @package App\Models */
class Set extends Model
{
    use HasFactory;

    protected $table = 'set';

    protected $fillable = ['name', 'description', 'user_id'];

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this set belongs to
     */
    public function user()
    {
        // Also method on user: eyepieces()
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get all of the eyepieces that are assigned this set.
     */
    public function eyepieces()
    {
        return $this->morphedByMany(Eyepiece::class, 'set_info');
    }

    /**
     * Get all of the filters that are assigned this set.
     */
    public function filters()
    {
        return $this->morphedByMany(Filter::class, 'set_info');
    }

    /**
     * Get all of the lenses that are assigned this set.
     */
    public function lenses()
    {
        return $this->morphedByMany(Lens::class, 'set_info');
    }

    /**
     * Get all of the instruments that are assigned this set.
     */
    public function instruments()
    {
        return $this->morphedByMany(Instrument::class, 'set_info');
    }
}
