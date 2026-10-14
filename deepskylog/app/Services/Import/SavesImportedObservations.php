<?php

namespace App\Services\Import;

use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\TargetAltitude;

/**
 * Duplicate check, horizon check and saving shared by the CSV and OAL importers.
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

    /** Whether observations of an object below the horizon are imported (with a warning). */
    private bool $importBelowHorizon = false;

    /** @var array<int, Location|null> */
    private array $horizonLocations = [];

    /**
     * Import observations of an object below the horizon of their location
     * too, instead of skipping them.
     */
    public function importBelowHorizon(bool $import = true): static
    {
        $this->importBelowHorizon = $import;

        return $this;
    }

    /**
     * Why the observation cannot be right, or null: the object stood below the
     * horizon of the location at that moment (UT), or, without a time, never
     * rises there on that date.
     */
    private function belowHorizonReason(array $fields, ?string $observedTime): ?string
    {
        $locationId = $fields['location_id'] ?? null;
        if (empty($locationId)) {
            return null;
        }
        if (!array_key_exists($locationId, $this->horizonLocations)) {
            $this->horizonLocations[$locationId] = Location::find($locationId);
        }
        $location = $this->horizonLocations[$locationId];

        $target = [
            'type' => (string) $fields['target_type'],
            'table' => (string) $fields['target_table'],
            'key' => (string) $fields['target_key'],
            'name' => (string) $fields['target_name'],
        ];
        $altitude = TargetAltitude::forObservation($target, $location, (string) $fields['observed_on'], $observedTime);
        if ($altitude === null || $altitude >= -TargetAltitude::TOLERANCE) {
            return null;
        }

        return $observedTime !== null
            ? sprintf("'%s' was %.1f° below the horizon of %s on %s at %s UT.", $target['name'], abs($altitude), $location->name, $fields['observed_on'], substr($observedTime, 0, 5))
            : sprintf("'%s' does not rise above the horizon of %s on %s.", $target['name'], $location->name, $fields['observed_on']);
    }

    /**
     * Apply the horizon check to an observation about to be saved: true when
     * it is to be skipped (reported as an error), false when it is saved (with
     * a warning when it is below the horizon but imported anyway).
     */
    private function skipBelowHorizon(array $fields, ?string $observedTime, ImportResult $result, string $label, string $context, int $row): bool
    {
        $reason = $this->belowHorizonReason($fields, $observedTime);
        if ($reason === null) {
            return false;
        }

        if ($this->importBelowHorizon) {
            $result->addWarning("{$label}: {$reason} Imported anyway.", $context, $row);

            return false;
        }

        $result->addError("{$label}: {$reason} Check the date, the time and the location, skipping. Tick \"Also import observations of objects below the horizon\" to import it anyway.", $context, $row);
        $result->skipped++;

        return true;
    }

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
