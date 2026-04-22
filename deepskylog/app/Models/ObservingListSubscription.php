<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservingListSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'observing_list_id',
    ];

    /**
     * Get the user who subscribed.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the observing list being subscribed to.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(ObservingList::class, 'observing_list_id');
    }
}
