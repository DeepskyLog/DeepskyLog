<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An observer invited to a starparty (#315), or answering whether they come:
 * going, maybe or declined. Invited is the status until they answer.
 */
class StarPartyParticipant extends Model
{
    public const INVITED = 'invited';

    public const GOING = 'going';

    public const MAYBE = 'maybe';

    public const DECLINED = 'declined';

    /** The answers an observer can give. */
    public const ANSWERS = [self::GOING, self::MAYBE, self::DECLINED];

    protected $fillable = ['star_party_id', 'user_id', 'status', 'invited_by_user_id'];

    public function starParty(): BelongsTo
    {
        return $this->belongsTo(StarParty::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }
}
