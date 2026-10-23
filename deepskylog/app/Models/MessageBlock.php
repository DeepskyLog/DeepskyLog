<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An observer (blocked_user_id) the user (user_id) doesn't accept messages
 * from. Administrators can't be blocked.
 */
class MessageBlock extends Model
{
    protected $fillable = ['user_id', 'blocked_user_id'];

    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }

    /**
     * Whether the receiver blocked the sender.
     */
    public static function blocks(User $receiver, User $sender): bool
    {
        return self::where('user_id', $receiver->id)->where('blocked_user_id', $sender->id)->exists();
    }
}
