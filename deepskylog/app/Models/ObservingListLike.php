<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservingListLike extends Model
{
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
}
