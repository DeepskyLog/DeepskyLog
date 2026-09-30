<?php

namespace App\Services\Import;

use App\Models\Observation;
use App\Models\User;

/**
 * Duplicate check and saving shared by the CSV and OAL importers.
 *
 * @property User $user
 */
trait SavesImportedObservations
{
    /**
     * Fields that an import does not change on an observation that is already
     * there: who made it and when (they are what matched), where a migrated
     * row came from, and whether it has a drawing (imports carry none).
     */
    private const KEPT_ON_UPDATE = [
        'observer_username',
        'observer_user_id',
        'observed_on',
        'observed_time',
        'has_drawing',
        'legacy_source',
    ];

    /**
     * The observation of this user of the same object at the same moment, or
     * null. Migrated observations count too, so re-importing an export does not
     * duplicate them.
     *
     * With a time, only an observation at that UT time matches; midnight UT
     * also matches an observation without a time, because that is how an OAL
     * export writes one (OAL requires a time). Without a time, only an
     * observation without a time matches: migrated ones have observed_time
     * 23:59:00 and legacy_time_int -9999.
     */
    private function findExistingObservation(string $targetType, string $targetKey, string $observedOn, ?string $observedTime): ?Observation
    {
        $base = fn() => Observation::query()
            ->where(fn($q) => $q->where('observer_user_id', $this->user->id)
                ->orWhere(fn($q) => $q->whereNull('observer_user_id')->where('observer_username', $this->user->username)))
            ->where('target_type', $targetType)
            ->where('target_key', $targetKey)
            ->where('observed_on', $observedOn)
            ->orderBy('id');

        $withoutTime = fn($q) => $q->where(fn($q) => $q->whereNull('observed_time')
            ->orWhere(fn($q) => $q->where('legacy_time_int', '<', 0)->where('observed_time', 'like', '23:59%')));

        if ($observedTime === null) {
            return $base()->where($withoutTime)->first();
        }

        return $base()->where('observed_time', $observedTime)->first()
            ?? ($observedTime === '00:00:00' ? $base()->where($withoutTime)->first() : null);
    }

    /**
     * Create the observation, or update the one that is already there.
     *
     * @param  array<string, mixed>  $fields
     * @return array{0: Observation, 1: bool} the observation and whether it existed
     */
    private function saveImportedObservation(array $fields, ?string $observedTime): array
    {
        $existing = $this->findExistingObservation(
            (string) $fields['target_type'],
            (string) $fields['target_key'],
            (string) $fields['observed_on'],
            $observedTime,
        );

        if ($existing) {
            $existing->update(array_diff_key($fields, array_flip(self::KEPT_ON_UPDATE)));

            return [$existing, true];
        }

        return [Observation::create($fields), false];
    }
}
