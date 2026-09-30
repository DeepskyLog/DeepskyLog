<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservingListLike extends Model
{
    use ClearsResponseCache;
    use HasFactory;

    protected $fillable = [
        'observing_list_id',
        'user_id',
    ];

    /**
     * Get the observing list being liked.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(ObservingList::class, 'observing_list_id');
    }

    /**
     * Get the user who liked the list.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Guests only see public lists, so changes on a private list do not
     * clear the response cache.
     */
    protected function affectsCachedPages(): bool
    {
        return ObservingList::whereKey($this->observing_list_id)->where('public', true)->exists();
    }
}
