<?php

namespace App\Models;

use App\Casts\PurifiedHtml;
use App\Traits\ClearsResponseCache;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A club or group of observers (a local astronomy club, the observers of a
 * forum). An observer can be a member of several clubs. The club
 * administrators (club_members.role admin; the observer who made the club
 * is the first) edit the club, let in the observers who asked to join
 * (join_policy approval; open clubs let everybody in at once), invite
 * observers and set the club's challenges. The members share their public
 * observing lists with the club, optionally to edit them together
 * (club_observing_list.members_edit, ObservingList::isEditableBy()), and
 * organise its starparties; the club page ranks the members
 * (App\Services\ClubRanking).
 */
class Club extends Model
{
    use ClearsResponseCache;
    use Sluggable;

    public const OPEN = 'open';

    public const APPROVAL = 'approval';

    /** The most clubs an observer makes (administrators any number). */
    public const MAX_PER_CREATOR = 5;

    protected $fillable = ['name', 'description', 'website', 'place', 'country', 'join_policy', 'user_id'];

    protected $attributes = [
        'join_policy' => self::APPROVAL,
    ];

    protected $casts = [
        'description' => PurifiedHtml::class,
    ];

    /** @var array<int, ClubMember|null> the membership per user id, read once */
    private array $memberships = [];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'method' => fn (string $string, string $separator) => Str::slug(
                    html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    $separator
                ) ?: 'club',
            ],
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** The members, the observers who asked to join and those invited. */
    public function memberships(): HasMany
    {
        return $this->hasMany(ClubMember::class);
    }

    /** The members (not those who asked to join or were invited). */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_members')
            ->wherePivot('status', ClubMember::MEMBER)
            ->withPivot('role')
            ->withTimestamps();
    }

    /** The observing lists the members shared with the club. */
    public function observingLists(): BelongsToMany
    {
        return $this->belongsToMany(ObservingList::class, 'club_observing_list')
            ->withPivot('user_id', 'members_edit')
            ->withTimestamps();
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(ObservingChallenge::class);
    }

    public function starParties(): HasMany
    {
        return $this->hasMany(StarParty::class);
    }

    /** The clubs the user is a member of. */
    public function scopeOfMember(Builder $query, User $user): Builder
    {
        return $query->whereIn('id', ClubMember::select('club_id')->where('user_id', $user->id)->where('status', ClubMember::MEMBER));
    }

    public function isOpen(): bool
    {
        return $this->join_policy === self::OPEN;
    }

    /** The row of the user in the club: member, asked to join or invited; null when none. */
    public function membershipOf(?User $user): ?ClubMember
    {
        if ($user === null || ! $this->exists) {
            return null;
        }
        if (! array_key_exists($user->id, $this->memberships)) {
            $this->memberships[$user->id] = $this->memberships()->where('user_id', $user->id)->first();
        }

        return $this->memberships[$user->id];
    }

    public function isMember(?User $user): bool
    {
        return (bool) $this->membershipOf($user)?->isMember();
    }

    /** Whether the user is an administrator of this club. */
    public function isAdmin(?User $user): bool
    {
        return (bool) $this->membershipOf($user)?->isAdmin();
    }

    /** The club administrators and the administrators of DeepskyLog manage the club. */
    public function canBeManagedBy(?User $user): bool
    {
        return $user !== null && ($this->isAdmin($user) || $user->hasAdministratorPrivileges());
    }

    /** The administrators of the club. */
    public function admins(): BelongsToMany
    {
        return $this->members()->wherePivot('role', ClubMember::ROLE_ADMIN);
    }

    /**
     * The observer joins: a member at once when the club is open or they
     * were invited, else they ask to join and a club administrator decides.
     */
    public function join(User $user): ClubMember
    {
        $membership = $this->membershipOf($user) ?? new ClubMember(['club_id' => $this->id, 'user_id' => $user->id]);
        if (! $membership->exists || ! $membership->isMember()) {
            $membership->status = $this->isOpen() || $membership->status === ClubMember::INVITED ? ClubMember::MEMBER : ClubMember::REQUESTED;
            $membership->role   = ClubMember::ROLE_MEMBER;
            $membership->save();
        }

        return $this->memberships[$user->id] = $membership;
    }

    /** A club administrator lets in an observer who asked to join. */
    public function approve(ClubMember $membership): void
    {
        $membership->forceFill(['status' => ClubMember::MEMBER])->save();
        unset($this->memberships[$membership->user_id]);
    }

    /**
     * The observer is no longer in the club (they leave, decline an
     * invitation or are removed): they also leave the club's challenges that
     * have not ended. When the last administrator goes, the member who joined
     * first becomes administrator; a club without members is deleted.
     *
     * @return bool whether the club was deleted
     */
    public function remove(User $user): bool
    {
        $membership = $this->membershipOf($user);
        if ($membership === null) {
            return false;
        }

        return DB::transaction(function () use ($membership, $user) {
            $membership->delete();
            unset($this->memberships[$user->id]);

            $this->challenges()->where('ends_on', '>=', Carbon::today()->toDateString())->get()
                ->each(fn (ObservingChallenge $challenge) => $challenge->leave($user));

            $members = $this->memberships()->where('status', ClubMember::MEMBER);
            if (! $members->clone()->exists()) {
                $this->delete();

                return true;
            }
            if (! $members->clone()->where('role', ClubMember::ROLE_ADMIN)->exists()) {
                $members->clone()->orderBy('created_at')->orderBy('id')->first()
                    ->forceFill(['role' => ClubMember::ROLE_ADMIN])->save();
            }

            return false;
        });
    }

    /**
     * Make a member administrator or a plain member; the last administrator
     * stays one.
     *
     * @return bool whether the role changed
     */
    public function setRole(ClubMember $membership, string $role): bool
    {
        if (! $membership->isMember() || $membership->role === $role || ! in_array($role, [ClubMember::ROLE_ADMIN, ClubMember::ROLE_MEMBER], true)) {
            return false;
        }
        if ($role === ClubMember::ROLE_MEMBER && $this->admins()->count() <= 1) {
            return false;
        }
        $membership->forceFill(['role' => $role])->save();
        unset($this->memberships[$membership->user_id]);

        return true;
    }

    /** The public observing lists shared with the club, newest first. */
    public function sharedLists()
    {
        return $this->observingLists()
            ->where('observing_lists.public', true)
            ->with('owner:id,name,slug')
            ->withCount('items')
            ->orderByPivot('created_at', 'desc')
            ->get();
    }

    /** "Leuven, Belgium" in the language of the reader. */
    public function placeLabel(): string
    {
        $country = \App\Services\ObserverCountries::label($this->country);

        return collect([html_entity_decode((string) $this->place, ENT_QUOTES | ENT_HTML5, 'UTF-8'), $country])
            ->filter(fn ($part) => $part !== null && trim($part) !== '')
            ->implode(', ');
    }

    /** The description without markup, shortened. */
    public function excerpt(int $length = 200): string
    {
        $text = html_entity_decode(strip_tags((string) $this->description), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), $length);
    }

    protected static function booted(): void
    {
        // Through the models, so that the standings of the challenges are forgotten
        static::deleting(function (self $club) {
            $club->challenges()->get()->each->delete();
        });
    }
}
