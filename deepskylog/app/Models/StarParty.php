<?php

namespace App\Models;

use App\Casts\PurifiedHtml;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A starparty (#315): an observer (user_id, the organiser) invites observers
 * to observe together for one or more nights at a site. start_date and
 * end_date are the evenings of the first and the last night, in the time
 * zone of the site.
 *
 * A public starparty is listed for everybody and anyone may come; a private
 * one is only seen by the organiser, the invited observers, holders of an
 * e-mail invitation and the administrators. The site (name, coordinates,
 * time zone) is copied from one of the organiser's locations.
 */
class StarParty extends Model
{
    use Sluggable;

    public const PUBLIC = 'public';

    public const PRIVATE = 'private';

    /** The most nights of one starparty. */
    public const MAX_NIGHTS = 14;

    /**
     * The distances (km) the organiser chooses from: sessions and observations
     * this close to the site in the nights belong to the starparty.
     */
    public const RADIUS_OPTIONS = [1, 2, 5, 10, 25];

    public const DEFAULT_RADIUS_KM = 5;

    /** The most e-mail invitations of one starparty. */
    public const MAX_EMAIL_INVITATIONS = 50;

    /** The most starparties an observer organises that have not ended yet. */
    public const MAX_UPCOMING_PER_ORGANISER = 10;

    /** The participants get a reminder this many days before the first night. */
    public const REMINDER_DAYS = 3;

    protected $fillable = [
        'user_id', 'name', 'description', 'language', 'translations', 'visibility', 'start_date', 'end_date',
        'registration_deadline', 'max_participants', 'location_id', 'location_name', 'latitude', 'longitude',
        'elevation', 'timezone', 'country', 'venue', 'website', 'activity_radius_km', 'activity_participants_only',
        'cancelled_at', 'reminder_sent_at',
    ];

    protected $casts = [
        'description'                => PurifiedHtml::class,
        'translations'               => 'array',
        'start_date'                 => 'date',
        'end_date'                   => 'date',
        'registration_deadline'      => 'date',
        'max_participants'           => 'integer',
        'latitude'                   => 'float',
        'longitude'                  => 'float',
        'elevation'                  => 'float',
        'cancelled_at'               => 'datetime',
        'reminder_sent_at'           => 'datetime',
        'activity_radius_km'         => 'float',
        'activity_participants_only' => 'boolean',
    ];

    protected $attributes = [
        'activity_radius_km'         => self::DEFAULT_RADIUS_KM,
        'activity_participants_only' => true,
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'method' => fn (string $string, string $separator) => Str::slug(
                    html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    $separator
                ) ?: 'starparty',
            ],
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function organiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(StarPartyParticipant::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(StarPartyInvitation::class);
    }

    /** The starparties that have not ended yet: the last night is today or later. */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('end_date', '>=', today()->subDay()->toDateString());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('end_date', '<', today()->subDay()->toDateString());
    }

    /**
     * The starparties the user may see: the public ones, and the private ones
     * they organise or are invited to (all of them for administrators).
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user?->hasAdministratorPrivileges()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('visibility', self::PUBLIC);
            if ($user) {
                $q->orWhere('user_id', $user->id)
                    ->orWhereIn('id', StarPartyParticipant::select('star_party_id')->where('user_id', $user->id));
            }
        });
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::PUBLIC;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    /** The first evening, as a moment in the time zone of the site. */
    public function startsAt(): Carbon
    {
        return Carbon::parse($this->start_date->toDateString().' 12:00', $this->timezone ?: 'UTC');
    }

    /** The morning after the last night. */
    public function endsAt(): Carbon
    {
        return Carbon::parse($this->end_date->toDateString().' 12:00', $this->timezone ?: 'UTC')->addDay();
    }

    public function isPast(): bool
    {
        return $this->endsAt()->isPast();
    }

    public function isOngoing(): bool
    {
        return ! $this->isPast() && $this->startsAt()->isPast();
    }

    /**
     * The evenings (Y-m-d) of the nights of the starparty.
     *
     * @return list<string>
     */
    public function nights(): array
    {
        $nights = [];
        for ($night = $this->start_date->copy(); $night->lte($this->end_date) && count($nights) < self::MAX_NIGHTS; $night->addDay()) {
            $nights[] = $night->toDateString();
        }

        return $nights;
    }

    /** "13–15 November 2026" in the language of the reader. */
    public function dateRange(): string
    {
        $locale = app()->getLocale();
        $start  = $this->start_date->copy()->locale($locale);
        $end    = $this->end_date->copy()->locale($locale);

        if ($start->isSameDay($end)) {
            return $start->isoFormat('LL');
        }

        return $start->isoFormat('LL').' – '.$end->isoFormat('LL');
    }

    public function canBeViewedBy(?User $user, ?string $invitationToken = null): bool
    {
        if ($this->isPublic() || $this->canBeManagedBy($user)) {
            return true;
        }
        if ($user && $this->participants()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return $invitationToken !== null && $this->invitations()->where('token', $invitationToken)->exists();
    }

    /** The organiser and the administrators edit, cancel and delete a starparty. */
    public function canBeManagedBy(?User $user): bool
    {
        return $user !== null && ((int) $user->id === (int) $this->user_id || $user->hasAdministratorPrivileges());
    }

    /**
     * The organiser invites observers; to a public starparty the observers
     * who come may invite others too.
     */
    public function canInvite(?User $user): bool
    {
        if ($user === null || $this->isCancelled() || $this->isPast()) {
            return false;
        }
        if ($this->canBeManagedBy($user)) {
            return true;
        }

        return $this->isPublic() && $this->statusOf($user) === StarPartyParticipant::GOING;
    }

    /** The answer of the user: going, maybe, declined, invited, or null. */
    public function statusOf(?User $user): ?string
    {
        if ($user === null) {
            return null;
        }

        return $this->participants->firstWhere('user_id', $user->id)?->status;
    }

    public function goingCount(): int
    {
        return $this->participants->where('status', StarPartyParticipant::GOING)->count();
    }

    public function isFull(): bool
    {
        return $this->max_participants !== null && $this->goingCount() >= $this->max_participants;
    }

    /** Whether new observers can still say they come. */
    public function registrationOpen(): bool
    {
        return ! $this->isCancelled() && ! $this->isPast()
                                      && ($this->registration_deadline === null || ! today()->gt($this->registration_deadline));
    }

    /**
     * Whether the user may give the answer: an invited observer, or anybody
     * for a public starparty; going or maybe only while the registration is
     * open and, for going, there is room. Saying no is always possible.
     */
    public function canAnswer(?User $user, string $status): bool
    {
        if ($user === null || ! in_array($status, StarPartyParticipant::ANSWERS, true)) {
            return false;
        }
        $current = $this->statusOf($user);
        if (! $this->isPublic() && $current === null && (int) $user->id !== (int) $this->user_id) {
            return false;
        }
        if ($status === StarPartyParticipant::DECLINED) {
            return $current !== null;
        }
        if (! $this->registrationOpen()) {
            return false;
        }

        return $status !== StarPartyParticipant::GOING || $current === StarPartyParticipant::GOING || ! $this->isFull();
    }

    /** Copy the site from a location of the organiser. */
    public function useLocation(Location $location): void
    {
        $this->fill([
            'location_id'   => $location->id,
            'location_name' => html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'latitude'      => (float) $location->latitude,
            'longitude'     => (float) $location->longitude,
            'elevation'     => is_numeric($location->elevation) ? (float) $location->elevation : null,
            'timezone'      => $location->timezone ?: 'UTC',
            'country'       => $location->country,
        ]);
    }

    /**
     * The site as a location, for the calculations of the nights: the
     * location it was copied from while that still has the same coordinates
     * (with its sky quality), else a location that is not stored.
     */
    public function site(): Location
    {
        $location = $this->location_id ? Location::find($this->location_id) : null;
        if ($location && abs((float) $location->latitude - $this->latitude) < 1e-6 && abs((float) $location->longitude - $this->longitude) < 1e-6) {
            return $location;
        }

        $site = new Location([
            'name'      => $this->location_name,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
            'elevation' => $this->elevation ?? 0,
            'timezone'  => $this->timezone,
            'country'   => $this->country,
        ]);
        $site->limitingMagnitude = null;
        $site->skyBackground     = null;

        return $site;
    }

    /** The description in the language of the reader, when there is a translation. */
    public function displayDescription(): string
    {
        return (string) ($this->translation() ?? $this->description);
    }

    public function isTranslated(): bool
    {
        return $this->translation() !== null;
    }

    /** The description without markup, shortened. */
    public function excerpt(int $length = 200): string
    {
        $text = html_entity_decode(strip_tags($this->displayDescription()), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), $length);
    }

    /** The great-circle distance in kilometres between two points. */
    public static function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 6371.0 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** The distance (km) from the site within which sessions and observations count. */
    public function radiusKm(): float
    {
        return $this->activity_radius_km > 0 ? (float) $this->activity_radius_km : (float) self::DEFAULT_RADIUS_KM;
    }

    /**
     * The usernames of the observers whose sessions and observations count:
     * with activity_participants_only, the organiser and the observers who
     * come or may come; null for everybody.
     *
     * @return list<string>|null
     */
    public function activityObservers(): ?array
    {
        if (! $this->activity_participants_only) {
            return null;
        }

        return User::whereIn('id', $this->participants()
            ->whereIn('status', [StarPartyParticipant::GOING, StarPartyParticipant::MAYBE])
            ->select('user_id'))
            ->orWhere('id', $this->user_id)
            ->pluck('username')
            ->all();
    }

    /**
     * The starparties a session of the observer at the location on the date
     * belongs to: those whose nights hold the date, whose site is within
     * their distance and, when only the observers who come count, that the
     * observer comes to.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function atSiteOn(?Location $location, string $date, ?string $observer, ?User $viewer = null): \Illuminate\Support\Collection
    {
        if (! $location || ! $location->hasValidCoordinates()) {
            return collect();
        }

        return self::visibleTo($viewer)
            ->whereNull('cancelled_at')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get()
            ->filter(fn (self $party) => self::distanceKm($party->latitude, $party->longitude, (float) $location->latitude, (float) $location->longitude) <= $party->radiusKm())
            ->filter(function (self $party) use ($observer) {
                $observers = $party->activityObservers();

                return $observers === null || in_array(mb_strtolower((string) $observer), array_map('mb_strtolower', $observers), true);
            })
            ->values();
    }

    private function translation(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === $this->language) {
            return null;
        }
        $translation = $this->translations[$locale] ?? null;

        return is_string($translation) && trim($translation) !== '' ? $translation : null;
    }
}
