<?php

namespace App\Services\Import;

use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\TargetAltitude;
use App\Services\ObservingListItemNameResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Object lookup, duplicate check, horizon check and saving shared by the CSV
 * and OAL importers.
 *
 * @property User $user
 */
trait SavesImportedObservations
{
    /**
     * Fields that an import does not change on an observation that is already
     * there: who made it and when (they are what matched), where a migrated
     * row came from, and whether it has a drawing (attachImportedDrawing()
     * adds one, but never replaces one).
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
     * The tables a target name is looked up in, in this order: deep-sky
     * objects first, then the Sun and planets, lunar features (including
     * "Moon"), the moons of the planets, asteroids and comets.
     */
    private const TARGET_TABLES = ['objects', 'planets', 'lunar_features', 'moons', 'asteroids', 'cometobjects'];

    /** Whether observations of an object below the horizon are imported (with a warning). */
    private bool $importBelowHorizon = false;

    /** @var array<int, Location|null> */
    private array $horizonLocations = [];

    /** The drawings uploaded with the file. */
    private ?ImportDrawings $drawings = null;

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
     * The drawings uploaded with the file, which the observations name.
     */
    public function withDrawings(ImportDrawings $drawings): static
    {
        $this->drawings = $drawings;

        return $this;
    }

    /**
     * Give a saved observation the drawing its file names. An observation
     * that has a drawing keeps it, so importing a file again does not add
     * its drawings twice.
     *
     * @param  list<string>  $references  the drawings the observation names; only the first that is found is imported
     */
    private function attachImportedDrawing(Observation $observation, array $references, ImportResult $result, string $label, string $context, int $row): void
    {
        $references = array_values(array_filter(array_map('trim', $references), fn (string $reference) => $reference !== ''));
        if ($references === []) {
            return;
        }

        $this->drawings ??= ImportDrawings::none();

        if ($observation->has_drawing) {
            // Counted, so the drawing is not reported as unused.
            foreach ($references as $reference) {
                $this->drawings->markUsed($reference);
            }
            if (! $this->drawings->isEmpty()) {
                $result->addInfo("{$label}: The observation already has a drawing, kept it.", $context, $row);
            }

            return;
        }

        foreach ($references as $reference) {
            [$stored, $problem] = $this->drawings->store($reference);

            if ($stored !== null) {
                $observation->forceFill(['has_drawing' => true, 'drawing_filename' => $stored])->save();
                $result->drawings++;
                $result->addSuccess("{$label}: Added drawing '{$reference}'.", $context, $row);

                // Only one drawing per observation.
                foreach (array_slice($references, array_search($reference, $references, true) + 1) as $other) {
                    $this->drawings->markUsed($other);
                    $result->addWarning("{$label}: An observation has one drawing, did not import '{$other}'.", $context, $row);
                }

                return;
            }

            if ($problem !== null) {
                $result->addWarning("{$label}: {$problem}", $context, $row);
            }
        }
    }

    /**
     * Report the drawings no observation named, or that the file names
     * drawings while none were uploaded.
     */
    private function reportDrawings(ImportResult $result): void
    {
        if ($this->drawings === null) {
            return;
        }

        if ($this->drawings->isEmpty()) {
            if ($this->drawings->references() > 0) {
                $result->addInfo("The file names {$this->drawings->references()} drawings. Upload them with the file, or the file and the drawings in a zip file, to import them.");
            }

            return;
        }

        $result->unusedDrawings = $this->drawings->unused();
        if ($result->unusedDrawings !== []) {
            $result->addWarning(count($result->unusedDrawings) . ' uploaded drawings are not named by an imported observation and were not imported.');
        }
    }

    /**
     * The spellings of a name to look up: as written (in lower case), as a
     * slug, without spaces and hyphens, without leading zeros, and the
     * catalogue variants.
     *
     * @return list<string>
     */
    private function objectNameCandidates(string $name): array
    {
        $lower = mb_strtolower(trim($name));
        $slug = Str::slug($name, '-');
        $compact = str_replace([' ', '-'], '', $lower);
        $noZeros = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower) ?? $lower;

        return array_values(array_unique(array_filter([$lower, $slug, $compact, $noZeros, str_replace('-', '', $slug), ...$this->catalogueVariants($name)])));
    }

    /**
     * The spellings of a catalogue designation as DeepskyLog stores it ("M31",
     * "NGC0224", "N1275" are "M 31", "NGC 224", "NGC 1275"), in lower case and
     * as slugs, to look up next to the name as written.
     *
     * @return list<string>
     */
    private function catalogueVariants(string $name): array
    {
        $resolver = app(ObservingListItemNameResolver::class);
        $variants = [];
        foreach ($resolver->variants($resolver->clean($name)) as $variant) {
            $variants[] = mb_strtolower($variant);
            $variants[] = Str::slug($variant, '-');
        }

        return $variants;
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
     * The observation of this user of the same object at the same moment with
     * the same instrument, or null. Migrated observations count too, so
     * re-importing an export does not duplicate them.
     *
     * With a time, only an observation at that UT time matches; midnight UT
     * also matches an observation without a time, because that is how an OAL
     * export writes one (OAL requires a time). Without a time, only an
     * observation without a time matches: migrated ones have observed_time
     * 23:59:00 and legacy_time_int -9999.
     *
     * An observation with another instrument is another observation (the
     * object seen with the naked eye and through the telescope). One without
     * an instrument matches too, after one with the same instrument: it gets
     * the instrument of the file.
     */
    private function findExistingObservation(string $targetType, string $targetKey, string $observedOn, ?string $observedTime, ?int $instrumentId): ?Observation
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

        $moments = match (true) {
            $observedTime === null => [$withoutTime],
            $observedTime === '00:00:00' => [fn($q) => $q->where('observed_time', $observedTime), $withoutTime],
            default => [fn($q) => $q->where('observed_time', $observedTime)],
        };

        foreach ($moments as $moment) {
            $existing = ($instrumentId !== null ? $base()->where($moment)->where('instrument_id', $instrumentId)->first() : null)
                ?? $base()->where($moment)->whereNull('instrument_id')->first();
            if ($existing !== null) {
                return $existing;
            }
        }

        return null;
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
            isset($fields['instrument_id']) ? (int) $fields['instrument_id'] : null,
        );

        if ($existing) {
            $existing->update(array_diff_key($fields, array_flip(self::KEPT_ON_UPDATE)));

            return [$existing, true];
        }

        return [Observation::create($fields), false];
    }

    /**
     * @param  list<string>  $candidates
     * @return array{string, string, string, string}|null
     */
    private function resolveObjectInTable(string $table, array $candidates): ?array
    {
        return match ($table) {
            'objects' => $this->resolveDeepSkyObject($candidates),
            'planets' => $this->resolveNamedTarget('planets', $candidates, ['name', 'designation'], fn(object $row): array => [
                'planets',
                $this->targetKeyFromRow($row),
                'planet',
                (string) $row->name,
            ]),
            'moons' => $this->resolveNamedTarget('moons', $candidates, ['name', 'designation'], fn(object $row): array => [
                'moons',
                $this->targetKeyFromRow($row),
                'lunar',
                (string) $row->name,
            ]),
            'lunar_features' => $this->resolveNamedTarget('lunar_features', $candidates, ['name'], fn(object $row): array => [
                'lunar_features',
                $this->targetKeyFromRow($row),
                'lunar',
                (string) $row->name,
            ]),
            'asteroids' => $this->resolveNamedTarget('asteroids', $candidates, ['name', 'designation'], fn(object $row): array => [
                'asteroids',
                $this->targetKeyFromRow($row),
                'solarsystem',
                (string) $row->name,
            ]),
            // Comets are stored by id (see App\Models\Observation).
            'cometobjects' => $this->resolveNamedTarget('cometobjects', $candidates, ['name'], fn(object $row): array => [
                'cometobjects',
                (string) $row->id,
                'comet',
                (string) $row->name,
            ]),
            default => null,
        };
    }

    /**
     * @param  list<string>  $candidates
     * @return array{string, string, string, string}|null
     */
    private function resolveDeepSkyObject(array $candidates): ?array
    {
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) {
                return ['objects', (string) $on->objectname, 'deepsky', (string) $on->objectname];
            }
        }
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('objectname', $cand)->orWhere('altname', $cand)->first();
            if ($on) {
                return ['objects', (string) $on->objectname, 'deepsky', (string) $on->objectname];
            }
        }
        foreach ($candidates as $cand) {
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) {
                return ['objects', (string) $o->name, 'deepsky', (string) $o->name];
            }
        }
        foreach ($candidates as $cand) {
            $o = DB::table('objects')->where('name', $cand)->first();
            if ($o) {
                return ['objects', (string) $o->name, 'deepsky', (string) $o->name];
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $candidates
     * @param  list<string>  $nameColumns
     * @param  callable(object): array{string, string, string, string}  $mapper
     * @return array{string, string, string, string}|null
     */
    private function resolveNamedTarget(string $table, array $candidates, array $nameColumns, callable $mapper): ?array
    {
        foreach ($candidates as $cand) {
            try {
                $row = DB::table($table)->where('slug', $cand)->first();
            } catch (\Throwable $_) {
                $row = null;
            }

            if ($row) {
                return $mapper($row);
            }
        }

        foreach ($nameColumns as $column) {
            foreach ($candidates as $cand) {
                try {
                    $row = DB::table($table)->where($column, $cand)->first();
                } catch (\Throwable $_) {
                    $row = null;
                }

                if ($row) {
                    return $mapper($row);
                }
            }
        }

        return null;
    }

    private function targetKeyFromRow(object $row): string
    {
        $slug = trim((string) ($row->slug ?? ''));
        if ($slug !== '') {
            return $slug;
        }

        $name = trim((string) ($row->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        return (string) ($row->id ?? '');
    }
}
