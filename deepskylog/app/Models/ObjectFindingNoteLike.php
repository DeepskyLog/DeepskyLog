<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A like of a finding note by another observer. */
class ObjectFindingNoteLike extends Model
{
    protected $fillable = ['object_finding_note_id', 'user_id'];

    public function note(): BelongsTo
    {
        return $this->belongsTo(ObjectFindingNote::class, 'object_finding_note_id');
    }
}
