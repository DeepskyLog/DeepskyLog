<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An invitation to a starparty (#315) mailed to an address. The link in the
 * mail holds the token: it shows a private starparty, and once the receiver
 * logs in (or registers) they become an invited participant.
 */
class StarPartyInvitation extends Model
{
    protected $fillable = ['star_party_id', 'email', 'token', 'invited_by_user_id', 'accepted_by_user_id', 'accepted_at'];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function starParty(): BelongsTo
    {
        return $this->belongsTo(StarParty::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }
}
