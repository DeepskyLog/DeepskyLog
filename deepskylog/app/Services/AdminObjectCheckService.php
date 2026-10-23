<?php

namespace App\Services;

use App\Jobs\CheckObjectConstellations;
use App\Models\Constellation;
use App\Models\DeepskyObject;
use App\Models\Observation;
use App\Services\Observations\ObservationCardData;
use App\Support\SpreadsheetCell;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminObjectCheckService
{
    private const DISPLAY_LIMIT = 250;

    /** How many observations of an unknown target the page links to. */
    private const LINKS_PER_TARGET = 10;

    /** Status and result of the last constellation check (CheckObjectConstellations). */
    public const CONSTELLATION_STATUS_KEY = 'admin_check_objects:constellations';

    /** All mismatches of the last constellation check, on the local disk. */
    public const CONSTELLATION_EXPORT_PATH = 'object-checks/constellation-mismatches.csv';

    /**
     * A check that has not reported progress for this long is taken to have
     * died with its worker, and may be queued again.
     */
    private const CONSTELLATION_STALE_MINUTES = 60;

    /**
     * The checks that are quick enough to run in the request; the
     * constellation check runs on the queue, see constellationCheckStatus().
     */
    public function buildReport(): array
    {
        return [
            'orphan_objectnames' => $this->buildOrphanObjectNamesCheck(),
            'unknown_observations' => $this->buildUnknownObservationsCheck(),
        ];
    }

    public function deleteOrphanObjectNames(): int
    {
        $ids = DB::table('objectnames')
            ->leftJoin('objects', 'objectnames.objectname', '=', 'objects.name')
            ->whereNull('objects.name')
            ->orderBy('objectnames.id')
            ->pluck('objectnames.id');

        if ($ids->isEmpty()) {
            return 0;
        }

        return DB::table('objectnames')->whereIn('id', $ids)->delete();
    }

    /**
     * The status of the last constellation check, null when none was queued:
     * state (queued, running, complete or failed), repair, total, checked,
     * skipped, mismatch_count, repaired, the first DISPLAY_LIMIT mismatches
     * as examples, and when it was queued, started, last updated and finished.
     */
    public function constellationCheckStatus(): ?array
    {
        $status = Cache::get(self::CONSTELLATION_STATUS_KEY);

        return is_array($status) ? $status : null;
    }

    public function constellationCheckInProgress(): bool
    {
        $status = $this->constellationCheckStatus();

        return $status !== null
            && in_array($status['state'], ['queued', 'running'], true)
            && Carbon::parse($status['updated_at'])->gt(now()->subMinutes(self::CONSTELLATION_STALE_MINUTES));
    }

    /**
     * Queue a check of the constellations of all objects, which corrects them
     * when $repair is set. Returns false when a check is already in progress.
     */
    public function queueConstellationCheck(bool $repair): bool
    {
        if ($this->constellationCheckInProgress()) {
            return false;
        }

        $this->putConstellationStatus([
            'state' => 'queued',
            'repair' => $repair,
            'queued_at' => now()->toIso8601String(),
            'display_limit' => self::DISPLAY_LIMIT,
        ]);

        CheckObjectConstellations::dispatch($repair);

        return true;
    }

    /**
     * Compare the stored constellation of every object with the one of its
     * coordinates, write all mismatches to CONSTELLATION_EXPORT_PATH and
     * correct them when $repair is set. Objects whose coordinates can't be
     * parsed are counted as skipped. Run by CheckObjectConstellations.
     */
    public function runConstellationCheck(bool $repair): array
    {
        $constellations = Constellation::query()->pluck('name', 'id')->map(fn($name) => (string) $name);
        $previous = $this->constellationCheckStatus() ?? [];

        $status = [
            'state' => 'running',
            'repair' => $repair,
            'queued_at' => $previous['queued_at'] ?? now()->toIso8601String(),
            'started_at' => now()->toIso8601String(),
            'total' => DB::table('objects')->count(),
            'checked' => 0,
            'skipped' => 0,
            'mismatch_count' => 0,
            'repaired' => 0,
            'examples' => [],
            'display_limit' => self::DISPLAY_LIMIT,
        ];
        $this->putConstellationStatus($status);

        $disk = Storage::disk('local');
        $disk->makeDirectory(dirname(self::CONSTELLATION_EXPORT_PATH));
        $file = fopen($disk->path(self::CONSTELLATION_EXPORT_PATH), 'w');

        try {
            fwrite($file, $this->csvLine(['Object', 'Stored Code', 'Stored Name', 'Expected Code', 'Expected Name', 'RA', 'Declination']));

            // Only con changes, so the offsets of the chunks stay the same while repairing
            DB::table('objects')
                ->select('name', 'con', 'ra', 'decl')
                ->orderBy('name')
                ->chunk(500, function (Collection $objects) use ($constellations, $repair, $file, &$status) {
                    foreach ($objects as $object) {
                        $status['checked']++;

                        $raHours = $this->normalizeRaToHours($object->ra);
                        $declination = DeepskyObject::decToDecimal($object->decl);

                        if ($raHours === null || $declination === null) {
                            $status['skipped']++;

                            continue;
                        }

                        $expectedCode = strtoupper((new EquatorialCoordinates($raHours, $declination))->getConstellation());
                        $storedCode = strtoupper(trim((string) ($object->con ?? '')));

                        if ($storedCode === $expectedCode) {
                            continue;
                        }

                        $status['mismatch_count']++;

                        $row = [
                            'name' => $object->name,
                            'stored_code' => $storedCode,
                            'stored_name' => $constellations->get($storedCode),
                            'expected_code' => $expectedCode,
                            'expected_name' => $constellations->get($expectedCode),
                            'ra' => DeepskyObject::formatRa($object->ra),
                            'decl' => DeepskyObject::formatDec($object->decl),
                        ];

                        fwrite($file, $this->csvLine(array_values($row)));

                        if (count($status['examples']) < self::DISPLAY_LIMIT) {
                            $status['examples'][] = $row;
                        }

                        if ($repair && DB::table('objects')->where('name', $object->name)->update(['con' => $expectedCode]) > 0) {
                            $status['repaired']++;
                        }
                    }

                    $this->putConstellationStatus($status);
                });
        } finally {
            fclose($file);
        }

        // The worker usually runs as another user than the web server, which
        // must still be able to read the file to offer the download.
        @chmod($disk->path(dirname(self::CONSTELLATION_EXPORT_PATH)), 0755);
        @chmod($disk->path(self::CONSTELLATION_EXPORT_PATH), 0644);

        if ($repair && $status['repaired'] > 0) {
            Cache::forget('admin_check_objects_report');
        }

        $status['state'] = 'complete';
        $status['finished_at'] = now()->toIso8601String();
        $this->putConstellationStatus($status);

        return $status;
    }

    public function markConstellationCheckFailed(): void
    {
        $this->putConstellationStatus(['state' => 'failed', 'finished_at' => now()->toIso8601String()] + ($this->constellationCheckStatus() ?? []));
    }

    /**
     * All mismatches of the last complete constellation check as CSV, null
     * when there is none.
     */
    public function exportConstellationMismatches(): ?string
    {
        if (($this->constellationCheckStatus()['state'] ?? null) !== 'complete') {
            return null;
        }

        $csv = Storage::disk('local')->get(self::CONSTELLATION_EXPORT_PATH);

        return is_string($csv) ? rtrim($csv, "\n") : null;
    }

    private function putConstellationStatus(array $status): void
    {
        $status['updated_at'] = now()->toIso8601String();

        Cache::forever(self::CONSTELLATION_STATUS_KEY, $status);
    }

    public function exportOrphanObjectNames(): string
    {
        $report = $this->buildOrphanObjectNamesCheck();

        $csv = $this->csvLine(['ID', 'Object Name', 'Catalog', 'Index', 'Alternative Name']);

        foreach ($report['examples'] as $row) {
            $csv .= $this->csvLine([$row->id, $row->objectname, $row->catalog, $row->catindex, $row->altname]);
        }

        return rtrim($csv, "\n");
    }

    public function exportAliasFixableObservationMappings(): string
    {
        $csv = $this->csvLine(['Observed Name', 'Primary Name', 'Observation Count']);

        if (!Schema::hasTable('observations')) {
            return rtrim($csv, "\n");
        }

        [$knownObjects, $primaryNameFor] = $this->primaryNameResolver();

        $this->deepskyObservations()
            ->select('target_key as objectname', DB::raw('COUNT(*) as observation_count'))
            ->groupBy('target_key')
            ->orderBy('target_key')
            ->chunk(500, function (Collection $rows) use (&$csv, $knownObjects, $primaryNameFor) {
                foreach ($rows as $row) {
                    $observedName = (string) ($row->objectname ?? '');
                    $normalized = $this->normalizeObjectNameKey($observedName);

                    if ($normalized === '' || isset($knownObjects[$normalized])) {
                        continue;
                    }

                    $primaryName = $primaryNameFor($normalized);

                    if ($primaryName === null) {
                        continue;
                    }

                    $csv .= $this->csvLine([$observedName, $primaryName, (int) $row->observation_count]);
                }
            });

        return rtrim($csv, "\n");
    }

    public function repairObservationObjectNamesToPrimary(): int
    {
        if (!Schema::hasTable('observations')) {
            return 0;
        }

        [$knownObjects, $primaryNameFor] = $this->primaryNameResolver();

        $updated = 0;

        // The names are read first: renaming rows while chunking would shift the offsets
        $observedNames = $this->deepskyObservations()
            ->whereNotNull('target_key')
            ->distinct()
            ->pluck('target_key');

        foreach ($observedNames as $observedName) {
            $rawObjectName = (string) $observedName;
            $normalized = $this->normalizeObjectNameKey($rawObjectName);

            if ($normalized === '' || isset($knownObjects[$normalized])) {
                continue;
            }

            $primaryName = $primaryNameFor($normalized);

            if ($primaryName === null || $rawObjectName === $primaryName) {
                continue;
            }

            $updated += $this->deepskyObservations()
                ->where('target_key', $rawObjectName)
                ->update(['target_table' => 'objects', 'target_key' => $primaryName, 'target_name' => $primaryName]);
            ObjectObservationStatistics::refreshObjects([$rawObjectName, $primaryName]);
        }

        return $updated;
    }

    private function buildOrphanObjectNamesCheck(): array
    {
        $query = DB::table('objectnames')
            ->leftJoin('objects', 'objectnames.objectname', '=', 'objects.name')
            ->whereNull('objects.name');

        return [
            'count' => (clone $query)->count(),
            'examples' => (clone $query)
                ->select('objectnames.id', 'objectnames.objectname', 'objectnames.catalog', 'objectnames.catindex', 'objectnames.altname')
                ->orderBy('objectnames.objectname')
                ->orderBy('objectnames.catalog')
                ->limit(self::DISPLAY_LIMIT)
                ->get(),
            'display_limit' => self::DISPLAY_LIMIT,
        ];
    }

    private function buildUnknownObservationsCheck(): array
    {
        try {
            if (!Schema::hasTable('observations')) {
                return $this->unavailableObservationsResult('The observations table is not available.');
            }

            [$knownObjects, $primaryNameFor] = $this->primaryNameResolver();

            $examples = [];
            $unknownCount = 0;
            $unknownObservationTotal = 0;
            $aliasCount = 0;
            $aliasObservationTotal = 0;

            $this->deepskyObservations()
                ->select('target_key as objectname', DB::raw('COUNT(*) as observation_count'))
                ->groupBy('target_key')
                ->orderBy('target_key')
                ->chunk(500, function (Collection $rows) use ($knownObjects, $primaryNameFor, &$examples, &$unknownCount, &$unknownObservationTotal, &$aliasCount, &$aliasObservationTotal) {
                    foreach ($rows as $row) {
                        $objectName = (string) ($row->objectname ?? '');
                        $normalized = $this->normalizeObjectNameKey($objectName);
                        $observationCount = (int) $row->observation_count;

                        if ($normalized === '' || isset($knownObjects[$normalized])) {
                            continue;
                        }

                        $primaryName = $primaryNameFor($normalized);

                        if ($primaryName !== null) {
                            $aliasCount++;
                            $aliasObservationTotal += $observationCount;
                        } else {
                            $unknownCount++;
                            $unknownObservationTotal += $observationCount;
                        }

                        if (count($examples) >= self::DISPLAY_LIMIT) {
                            continue;
                        }

                        $examples[] = [
                            'objectname' => $objectName,
                            'primary_name' => $primaryName,
                            'status' => $primaryName !== null ? 'alias' : 'unknown',
                            'observation_count' => $observationCount,
                        ];
                    }
                });

            $examples = $this->addUnknownTargetLinks($examples);

            return [
                'available' => true,
                'count' => $unknownCount,
                'observation_total' => $unknownObservationTotal,
                'alias_count' => $aliasCount,
                'alias_observation_total' => $aliasObservationTotal,
                'examples' => $examples,
                'display_limit' => self::DISPLAY_LIMIT,
                'message' => null,
            ];
        } catch (\Throwable $throwable) {
            return $this->unavailableObservationsResult($throwable->getMessage());
        }
    }

    /**
     * Give the unknown targets among the examples the observations that use
     * them (observer, date, url) and the objects whose name is theirs plus a
     * letter ("IC 2554A" and "IC 2554B" for "IC 2554"), with the url of their
     * page, so the administrator can look up which object was meant.
     */
    private function addUnknownTargetLinks(array $examples): array
    {
        $unknownNames = collect($examples)->where('status', 'unknown')->pluck('objectname')->all();

        if ($unknownNames === []) {
            return $examples;
        }

        $observations = Observation::query()
            ->where('target_type', Observation::TYPE_DEEPSKY)
            ->whereIn('target_key', $unknownNames)
            ->orderBy('observed_on')
            ->get()
            ->groupBy(fn(Observation $observation) => $this->normalizeObjectNameKey((string) $observation->target_key));
        ObservationCardData::instance()->preloadDetailUrls($observations->flatten());

        foreach ($examples as &$example) {
            if ($example['status'] !== 'unknown') {
                continue;
            }

            $example['observations'] = $observations->get($this->normalizeObjectNameKey($example['objectname']), collect())
                ->take(self::LINKS_PER_TARGET)
                ->map(fn(Observation $observation) => [
                    'observer' => (string) $observation->observer_username,
                    'date' => $observation->observed_on?->format('Y-m-d'),
                    'url' => $observation->detailUrl(),
                ])
                ->all();

            $example['candidates'] = collect(DeepskyObject::componentsOf($example['objectname']))
                ->map(fn($slug, $name) => ['name' => $name, 'url' => route('object.show', ['slug' => $slug])])
                ->values()
                ->all();
        }

        return $examples;
    }

    /**
     * Deep-sky observations; target_key holds the name of the object (see the
     * target scheme in App\Models\Observation).
     */
    private function deepskyObservations(): \Illuminate\Database\Query\Builder
    {
        return DB::table('observations')->where('target_type', Observation::TYPE_DEEPSKY);
    }

    private function unavailableObservationsResult(string $message): array
    {
        return [
            'available' => false,
            'count' => 0,
            'observation_total' => 0,
            'alias_count' => 0,
            'alias_observation_total' => 0,
            'examples' => [],
            'display_limit' => self::DISPLAY_LIMIT,
            'message' => $message,
        ];
    }

    /**
     * The objects (normalized name => name) and a function that gives the
     * object an unknown observed name stands for, or null: the object of
     * which it is an alternative name, else the one object or alternative
     * name that differs only in spaces ("NGC1741B" for "NGC 1741B").
     *
     * @return array{0: array<string, string>, 1: callable(string): ?string}
     */
    private function primaryNameResolver(): array
    {
        $knownObjects = $this->buildKnownObjectsMap();
        $aliasToPrimary = $this->buildAliasToPrimaryMap($knownObjects);

        $compact = [];
        foreach ([$knownObjects, $aliasToPrimary] as $names) {
            foreach ($names as $key => $primary) {
                $compactKey = $this->compactObjectNameKey($key);
                // A spelling that fits several objects is left to the administrator
                $compact[$compactKey] = isset($compact[$compactKey]) && $compact[$compactKey] !== $primary ? false : $primary;
            }
        }

        return [
            $knownObjects,
            fn(string $normalized): ?string => $aliasToPrimary[$normalized] ?? ($compact[$this->compactObjectNameKey($normalized)] ?? null) ?: null,
        ];
    }

    private function compactObjectNameKey(string $normalized): string
    {
        return preg_replace('/\s+/u', '', $normalized);
    }

    private function buildKnownObjectsMap(): array
    {
        $known = [];

        DB::table('objects')
            ->select('name')
            ->orderBy('name')
            ->chunk(1000, function (Collection $rows) use (&$known) {
                foreach ($rows as $row) {
                    $name = (string) ($row->name ?? '');
                    $normalized = $this->normalizeObjectNameKey($name);

                    if ($normalized === '') {
                        continue;
                    }

                    $known[$normalized] = trim($name);
                }
            });

        return $known;
    }

    private function buildAliasToPrimaryMap(array $knownObjects): array
    {
        $map = [];

        DB::table('objectnames')
            ->join('objects', 'objectnames.objectname', '=', 'objects.name')
            ->select('objectnames.altname', 'objectnames.objectname')
            ->whereNotNull('objectnames.altname')
            ->orderBy('objectnames.id')
            ->chunk(1000, function (Collection $rows) use (&$map, $knownObjects) {
                foreach ($rows as $row) {
                    $alias = (string) ($row->altname ?? '');
                    $primary = trim((string) ($row->objectname ?? ''));

                    if ($primary === '') {
                        continue;
                    }

                    $aliasKey = $this->normalizeObjectNameKey($alias);
                    $primaryKey = $this->normalizeObjectNameKey($primary);

                    if ($aliasKey === '' || $aliasKey === $primaryKey || !isset($knownObjects[$primaryKey])) {
                        continue;
                    }

                    if (!isset($map[$aliasKey])) {
                        $map[$aliasKey] = $knownObjects[$primaryKey];
                    }
                }
            });

        return $map;
    }

    /**
     * One CSV line with every cell quoted, text a spreadsheet would read as a
     * formula made text.
     */
    private function csvLine(array $cells): string
    {
        return implode(',', array_map(
            fn($cell) => '"' . str_replace('"', '""', (string) ($cell ?? '')) . '"',
            SpreadsheetCell::escapeRow($cells)
        )) . "\n";
    }

    private function normalizeObjectNameKey(string $name): string
    {
        return strtoupper(trim($name));
    }

    private function normalizeRaToHours(mixed $ra): ?float
    {
        if ($ra === null || $ra === '') {
            return null;
        }

        $value = trim((string) $ra);

        if (is_numeric($value)) {
            $numeric = (float) $value;

            return $numeric > 24.0 ? $numeric / 15.0 : $numeric;
        }

        $decimal = DeepskyObject::raToDecimal($value);

        if ($decimal === null) {
            return null;
        }

        return $decimal / 15.0;
    }
}