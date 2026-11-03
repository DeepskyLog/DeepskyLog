<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An observer in a club: a member (role admin or member), or an observer who
 * asked to join (requested) or was invited by a club administrator (invited)
 * and has not been let in or accepted yet. The members are listed on the
 * (cached) observer pages.
 */
class ClubMember extends Model
{
    use ClearsResponseCache;

    public const MEMBER = 'member';

    public const REQUESTED = 'requested';

    public const INVITED = 'invited';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MEMBER = 'member';

    protected $fillable = ['club_id', 'user_id', 'role', 'status', 'invited_by_user_id'];

    protected $attributes = [
        'role'   => self::ROLE_MEMBER,
        'status' => self::MEMBER,
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    protected function affectsCachedPages(): bool
    {
        return $this->status === self::MEMBER;
    }

    public function isMember(): bool
    {
        return $this->status === self::MEMBER;
    }

    public function isAdmin(): bool
    {
        return $this->isMember() && $this->role === self::ROLE_ADMIN;
    }
}
