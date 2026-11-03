<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An observation that looks wrong, found by observations:check-suspect
 * (App\Services\Observations\SuspectObservations): REASON_BELOW_HORIZON,
 * REASON_TOO_FAINT or REASON_DUPLICATE (of related_observation_id). The
 * details hold what was found (altitude, contrast reserve, ...). A dismissed
 * one (the observer or an administrator said it is right) is no longer shown
 * to the observer and stays dismissed as long as the check finds it.
 */
class SuspectObservation extends Model
{
    public const REASON_BELOW_HORIZON = 'below_horizon';

    public const REASON_TOO_FAINT = 'too_faint';

    public const REASON_DUPLICATE = 'duplicate';

    public const REASONS = [self::REASON_BELOW_HORIZON, self::REASON_TOO_FAINT, self::REASON_DUPLICATE];

    protected $fillable = ['observation_id', 'reason', 'details', 'related_observation_id', 'dismissed_at', 'dismissed_by'];

    protected $casts = [
        'details' => 'array',
        'dismissed_at' => 'datetime',
    ];

    /**
     * The localized name of each reason.
     *
     * @return array<string, string>
     */
    public static function reasonLabels(): array
    {
        return [
            self::REASON_BELOW_HORIZON => __('Below the horizon'),
            self::REASON_TOO_FAINT => __('Too faint for the instrument'),
            self::REASON_DUPLICATE => __('Duplicate'),
        ];
    }

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    public function relatedObservation(): BelongsTo
    {
        return $this->belongsTo(Observation::class, 'related_observation_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('suspect_observations.dismissed_at');
    }

    /** The suspect observations of one observer. */
    public function scopeOfObserver(Builder $query, User $user): Builder
    {
        return $query->whereIn('suspect_observations.observation_id', Observation::query()
            ->select('id')
            ->where('observer_username', $user->username));
    }

    public function canBeManagedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }
        if ($user->hasModeratorRole()) {
            return true;
        }

        return $this->observation !== null && $this->observation->observer_username === $user->username;
    }

    public function dismiss(User $by): void
    {
        $this->forceFill(['dismissed_at' => now(), 'dismissed_by' => $by->id])->save();
    }

    public function restore(): void
    {
        $this->forceFill(['dismissed_at' => null, 'dismissed_by' => null])->save();
    }

    /**
     * The explanation of what was found, in the reader's language.
     */
    public function explanation(): string
    {
        $details = $this->details ?? [];

        return match ($this->reason) {
            self::REASON_BELOW_HORIZON => isset($details['moment'])
                ? __('The object was :altitude° below the horizon of :location at :moment UT.', [
                    'altitude' => number_format(abs((float) ($details['altitude'] ?? 0)), 1),
                    'location' => $details['location'] ?? '?',
                    'moment' => $details['moment'],
                ])
                : __('The object does not rise above the horizon of :location.', ['location' => $details['location'] ?? '?']),
            self::REASON_TOO_FAINT => __('The contrast reserve is :reserve with a :aperture mm instrument at :magnification× under a sky of SQM :sqm, while the object is marked as seen.', [
                'reserve' => number_format((float) ($details['contrast_reserve'] ?? 0), 2),
                'aperture' => (int) round((float) ($details['aperture'] ?? 0)),
                'magnification' => $details['magnification'] ?? '?',
                'sqm' => number_format((float) ($details['sqm'] ?? 0), 2),
            ]),
            self::REASON_DUPLICATE => __('Same object, date, time and equipment as another observation of the same observer.'),
            default => '',
        };
    }
}
