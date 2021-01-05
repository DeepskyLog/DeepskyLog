<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/** @package App\Models */
class Set extends Model
{
    use HasFactory;

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
}
