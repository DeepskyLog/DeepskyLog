<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * An observing request: an observer asks the others to observe a deep-sky
 * object ("NGC 7317 not seen with 8″, who can try with 14″?"), optionally
 * only with an aperture of at least min_aperture_mm. Keyed on objects.name
 * like the finding notes (ObjectMerger and the object edit page move them).
 *
 * A request is open until it is closed by its observer (or an administrator)
 * or its expires_on has passed. Every observation of the object by another
 * observer recorded while it is open, with a large enough aperture, seen or
 * not seen, is an answer (observing_request_answers, written by
 * App\Services\ObservingRequests::observationCreated(), which also tells the
 * observer who asked).
 */
class ObservingRequest extends Model
{
    use ClearsResponseCache;

    /** The longest note, in characters. */
    public const MAX_NOTE_LENGTH = 300;

    /** The open requests one observer may have. */
    public const MAX_OPEN_PER_USER = 10;

    /** How long a request stays open by default, and at most (months). */
    public const DEFAULT_MONTHS = 4;

    public const MAX_MONTHS = 12;

    /** The apertures to choose as minimum (mm), shown in inches to observers who use them. */
    public const APERTURES = [100, 150, 200, 250, 300, 350, 400, 500, 600];

    protected $fillable = ['object_name', 'user_id', 'note', 'min_aperture_mm', 'expires_on', 'closed_at', 'answered_at'];

    protected $casts = [
        'expires_on' => 'date',
        'closed_at' => 'datetime',
        'answered_at' => 'datetime',
        'min_aperture_mm' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The observations that answer the request, the newest first. */
    public function answers(): BelongsToMany
    {
        return $this->belongsToMany(Observation::class, 'observing_request_answers')
            ->withTimestamps()
            ->orderByDesc('observing_request_answers.id');
    }

    /** The requests not closed and not expired. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('observing_requests.closed_at')
            ->whereDate('observing_requests.expires_on', '>=', now()->toDateString());
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null && $this->expires_on !== null && ! $this->expires_on->lt(now()->startOfDay());
    }

    /** The note as stored: whitespace collapsed, without tags. */
    public static function clean(?string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $text)));
    }

    /**
     * A new request of $user. Throws a ValidationException (fields `note`,
     * `min_aperture_mm`, `months`) for a note that is too long, an aperture
     * that is not offered, a period out of range, an object that is not in
     * DeepskyLog, an open request of the same observer for the object, or
     * MAX_OPEN_PER_USER open requests.
     */
    public static function ask(User $user, string $objectName, ?string $note, ?int $minApertureMm, int $months = self::DEFAULT_MONTHS): self
    {
        $note = self::clean($note);
        if (mb_strlen($note) > self::MAX_NOTE_LENGTH) {
            throw ValidationException::withMessages(['note' => __('A note has at most :count characters.', ['count' => self::MAX_NOTE_LENGTH])]);
        }
        if ($minApertureMm !== null && ! in_array($minApertureMm, self::APERTURES, true)) {
            throw ValidationException::withMessages(['min_aperture_mm' => __('Choose one of the apertures offered.')]);
        }
        if ($months < 1 || $months > self::MAX_MONTHS) {
            throw ValidationException::withMessages(['months' => __('A request stays open between 1 and :count months.', ['count' => self::MAX_MONTHS])]);
        }
        $object = DeepskyObject::where('name', $objectName)->first(['name']);
        if (! $object) {
            throw ValidationException::withMessages(['note' => __('This object is not in DeepskyLog.')]);
        }
        if (self::open()->where('user_id', $user->id)->where('object_name', $object->name)->exists()) {
            throw ValidationException::withMessages(['note' => __('You already asked for observations of this object.')]);
        }
        if (self::open()->where('user_id', $user->id)->count() >= self::MAX_OPEN_PER_USER) {
            throw ValidationException::withMessages(['note' => __('You can have at most :count open observing requests.', ['count' => self::MAX_OPEN_PER_USER])]);
        }

        return self::create([
            'object_name' => $object->name,
            'user_id' => $user->id,
            'note' => $note === '' ? null : $note,
            'min_aperture_mm' => $minApertureMm,
            'expires_on' => Carbon::today()->addMonthsNoOverflow($months),
        ]);
    }

    /** The observer who asked, administrators and database experts close a request. */
    public function canBeClosedBy(?User $user): bool
    {
        return $user !== null && ($user->id === $this->user_id || $user->hasAdministratorPrivileges());
    }

    public function close(): void
    {
        $this->forceFill(['closed_at' => now()])->save();
    }

    /**
     * Whether an observation answers the request: of the object, by another
     * observer, recorded while the request is open, with an instrument of at
     * least the aperture asked for.
     */
    public function isAnsweredBy(Observation $observation, ?float $apertureMm): bool
    {
        if (! $this->isOpen() || (int) $observation->observer_user_id === (int) $this->user_id) {
            return false;
        }

        return $this->min_aperture_mm === null || ($apertureMm !== null && $apertureMm >= $this->min_aperture_mm);
    }

    /** The aperture asked for, in inches or mm as the viewer reads them. */
    public static function apertureLabel(int $mm, ?User $viewer): string
    {
        return $viewer?->showInches
            ? round($mm / 25.4) . '″'
            : $mm . ' mm';
    }

    /**
     * Move the requests of an object to another one (a renamed or merged
     * object).
     */
    public static function moveObject(string $from, string $to): void
    {
        if ($from !== $to) {
            self::where('object_name', $from)->update(['object_name' => $to]);
            self::clearResponseCache();
        }
    }
}
