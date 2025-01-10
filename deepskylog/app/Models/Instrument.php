<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instrument extends Model
{
    protected $fillable = [
        'user_id', 'name', 'diameter', 'type',
        'fd', 'fixedMagnification', 'active',
    ];

    protected $with = ['make', 'mount_type', 'instrument_type'];

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this instrument belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Returns the name of the instrument type.
     *
     * @return string the name of the instrument type
     */
    public function fullName(): string
    {
        return ltrim($this->make->name.' '.$this->name);
    }

    public function make(): BelongsTo
    {
        return $this->belongsTo('App\Models\InstrumentMake');
    }

    public function mount_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\MountType');
    }

    public function instrument_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\InstrumentType');
    }
}
