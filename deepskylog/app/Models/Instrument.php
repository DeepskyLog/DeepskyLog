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

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this instrument belongs to
     */
    public function user(): BelongsTo
    {
        // Also method on user: instruments()
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Returns the name of the instrument type.
     *
     * @return string the name of the instrument type
     */
    public function typeName(): string
    {
        return InstrumentType::where('id', $this->type)->value('type');
    }
}
